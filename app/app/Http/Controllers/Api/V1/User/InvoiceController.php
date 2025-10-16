<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\InvoiceResource;
use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_id' => ['nullable', 'exists:buildings,id'],
            'unverified' => ['nullable', 'boolean'],
            'unit' => ['nullable', 'exists:building_units,id']
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoices = Invoice::where('status', 'paid')
            ->whereNot('serviceable_type', 'App\Models\Commission')
            ->where(function ($query) {
                $query->where('user_id', auth()->user()->id)
                    ->orWhere(function ($query) {
                        $query->where('serviceable_type', 'App\Models\BuildingUnit')
                            ->whereIn('serviceable_id', auth()->user()->building_units->pluck('id'));
                    });
            });

        $user = auth()->user();
        foreach ($user->building_units as $unit) {
            if ($unit->building->name_en == 'hshcomplex') {
                $invoices = $invoices->where('created_at', '>', Carbon::parse('2024-08-03 00:00'));
            }
        }

        if ($request->has('building_id')) {
            $invoices = $invoices->where('building_id', $request->building_id)
                ->whereNot('serviceable_type', 'App\Models\Commission');
        }


        if ($request->has('unit')) {
            $unit = BuildingUnit::findOrfail($request->unit);
            $ownership = $unit->residents()->where('user_id', auth()->user()->id)->first()->pivot->ownership;

            $resident_type = $ownership;
            if ($ownership == 'owner' && $unit->residents()->count() == 1) {
                $resident_type = 'resident';
            }
            if ($ownership == 'renter') {
                $resident_type = 'resident';
            }
            $invoices = $invoices->where('serviceable_type', 'App\Models\BuildingUnit')
                ->where('serviceable_id', $request->unit)
                ->where(function ($query) use ($resident_type, $ownership) {
                    $query->where('resident_type', 'all')
                        ->orWhere('resident_type', $ownership)
                        ->orWhere('resident_type', $resident_type);
                });
        }

        if ($request->has('unverified') && $request->unverified) {
            $invoices = $invoices->where('is_verified', 0);
        }
        $invoices = $invoices->orderBy('id', 'desc');
        $invoices = $invoices->get();
        return response()->json([
            'success' => true,
            'data' => [
                'invoices' => InvoiceResource::collection($invoices),
            ]
        ]);
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->user_id != auth()->user()->id && !auth()->user()->building_units->pluck('id')->contains($invoice->serviceable_id)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'invoice' => 'شما اجازه دسترسی به این صورتحساب را ندارید.'
                ]
            ]);
        }

        if ($invoice->status == 'pending') {
            return abort(404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => new InvoiceResource($invoice),
            ]
        ]);
    }
}
