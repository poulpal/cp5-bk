<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\InvoiceResource;
use App\Models\Building;
use App\Models\BuildingManager;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class CostController extends Controller
{
    private function getInvoices($request)
    {
        $validator = Validator::make(request()->all(), [
            'type' => 'nullable|in:cost,income',
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'unit' => 'required|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $unit = request()->unit ? auth()->user()->building_units()->find(request()->unit) : null;
        if (!$unit) {
            return response()->json([
                'success' => false,
                'errors' => 'unit not found'
            ], 404);
        }
        $building = $unit->building;

        if ($building->options->show_costs_to_units == false) {
            return abort(200);
        }

        $invoices = $unit->building->invoices()->where('status', 'paid')->where('show_units', true);
        $invoices = $invoices->whereNot('serviceable_type', Commission::class);

        $invoices = $invoices->where('amount', '<', 0)->where('serviceable_type', Building::class);


        if (request()->has('sort') && request()->sort) {
            if ($request->sort == 'unit_number') {
                $invoices = $invoices->join('building_units', 'building_units.id', '=', 'invoices.serviceable_id')
                    ->orderBy('building_units.unit_number', $request->order ?? 'desc');
            } else {
                $invoices = $invoices->orderBy(request()->sort, request()->order ?? 'desc');
            }
        } else {
            $invoices = $invoices->orderBy('created_at', 'desc');
        }

        if (request()->has('filters') && request()->filters) {
            $filters = json_decode(request()->filters);
            foreach ($filters as $filter) {
                $key = $filter->columnName;
                $value = $filter->value;
                if ($key == 'amount') {
                    $invoices = $invoices->where($key, floatval($value));
                } elseif ($key == 'created_at') {
                    $date_start = Carbon::parse($value)->startOfDay();
                    $date_end = Carbon::parse($value)->endOfDay();
                    $invoices = $invoices->where('invoices.created_at', '>=', $date_start)->where('invoices.created_at', '<=', $date_end);
                } elseif ($key == 'unit_number') {
                    $invoices = $invoices->whereHas('unit', function ($query) {
                        $query->where('unit_number', 'like', '%' . request()->search . '%');
                    });
                } else {
                    $invoices = $invoices->where('invoices.' . $key, 'like', '%' . $value . '%');
                }
            }
        }

        $invoices_query = clone $invoices;

        if (request()->has('start_date') && request()->start_date && request()->has('end_date') && request()->end_date) {
            $start = Carbon::parse(request()->start_date)->startOfDay();
            $end = Carbon::parse(request()->end_date)->endOfDay();
            $invoices = $invoices->where('created_at', '>=', $start)->where('created_at', '<=', $end);
        }

        if (request()->has('paginate') && request()->paginate) {
            $invoices = $invoices->paginate(request()->perPage ?? 20);
        } else {
            $invoices = $invoices->get();
        }

        if (request()->has('start_date') && request()->start_date && request()->has('end_date') && request()->end_date) {
            $balance = $invoices_query->where('created_at', '<', Carbon::parse(request()->start_date)->startOfDay())->sum('amount');
            $remainder_balance = $balance;
        } else {
            $balance = 0;
            $remainder_balance = 0;
        }

        foreach ($invoices->reverse() as $invoice) {
            $balance += $invoice->amount;
            $invoice->balance = round($balance, 1);
        }

        if ($remainder_balance != 0 && $request->type == null) {
            $remainder = clone $invoices_query->where('created_at', '<', Carbon::parse(request()->start_date)->startOfDay())->first();
            if ($remainder) {
                $remainder->id = null;
                $remainder->amount = $remainder_balance;
                $remainder->description = __("مانده دوره قبل");
                $remainder->created_at = Carbon::parse(request()->start_date)->startOfDay();
                $remainder->balance = $remainder_balance;
                // add to end of invoices
                $invoices->push($remainder);
            }
        }

        return $invoices;
    }

    public function index(Request $request)
    {

        $invoices = $this->getInvoices($request);

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($invoices, InvoiceResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => InvoiceResource::collection($invoices),
                ]
            ]);
        }
    }
}
