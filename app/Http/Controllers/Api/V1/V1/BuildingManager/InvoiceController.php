<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

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

class InvoiceController extends Controller
{

    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show', 'pdf', 'addMultiple']);
    }

    private function getInvoices($request)
    {
        $validator = Validator::make(request()->all(), [
            'unit' => 'nullable|numeric|exists:building_units,id',
            'filter' => 'nullable|in:all,unverified,verified',
            'type' => 'nullable|in:debt,deposit,cost,income',
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
            'search' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }



        $invoices = auth()->buildingManager()->building->invoices()->where('status', 'paid')
            ->with('service')
            ->with('debtType')
            ->with('bank');
        $invoices = $invoices->whereNot('serviceable_type', Commission::class);

        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            $invoices = $invoices->where('created_at', '>=', Carbon::parse('2024-03-20 00:00'));
        }

        if (request()->has('resType') && request()->resType && request()->resType != 'all') {
            $invoices = $invoices->where('resident_type', request()->resType);
        }

        if (request()->has('unit')) {
            $invoices = $invoices->where('serviceable_id', request()->unit)->where('serviceable_type', BuildingUnit::class);
        }

        if (request()->has('filter')) {
            if (request()->filter == 'unverified') {
                $invoices = $invoices->where('is_verified', 0);
            } elseif (request()->filter == 'verified') {
                $invoices = $invoices->where('is_verified', 1);
            }
        }

        if (request()->has('type')) {
            if (request()->type == 'debt') {
                $invoices = $invoices->where('amount', '<', 0)->where('serviceable_type', BuildingUnit::class);
            } elseif (request()->type == 'deposit') {
                $invoices = $invoices->where('amount', '>', 0)->where('serviceable_type', BuildingUnit::class);
            }
            elseif (request()->type == 'cost') {
                $invoices = $invoices->where('amount', '<', 0)->where('serviceable_type', 'App\Models\Building');
            } elseif (request()->type == 'income') {
                $invoices = $invoices->where('amount', '>', 0)->where(function ($query) {
                    $query->where('serviceable_type', 'App\Models\Building')
                        ->orWhere('serviceable_type', BuildingUnit::class)
                        ->orWhere('serviceable_type', 'App\Models\Reservation');
                });
            }
        }

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

        if (request()->has('search') && request()->search && request()->search != '') {
            $invoices = $invoices->whereHas('unit', function ($query) {
                $query->where('unit_number', 'like', '%' . request()->search . '%');
            });
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

        // if(auth()->buildingManager()->building->name_en == 'hshcomplex' || auth()->buildingManager()->building->name_en == 'atishahr'){
        //     // $start_date = Carbon::parse('2023-12-30')->endofDay();
        //     $start_date = Carbon::parse('2024-05-02 11:00');
        //     $days = 2;
        //     $today = Carbon::now()->endofDay();
        //     if ($today->diff($start_date)->days >= $days){
        //         $limit = $today->addDays(-1 * $days);
        //     }else{
        //         $limit = $start_date;
        //     }
        //     $exception_units = [];
        //     $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
        //     $invoices = $invoices->whereNot(function ($query) use ($limit, $ids) {
        //         $query->whereNot('payment_method', 'cash')
        //         ->whereNotIn('serviceable_id', $ids)
        //         ->where('created_at', '>', $limit);
        //     });
        // }

        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            $start_date = Carbon::parse('2024-05-15 08:30');
            $ids = [];
            $ins = auth()->buildingManager()->building
                ->invoices()
                ->where('status', 'paid')
                ->where('is_verified', 1)
                ->where('serviceable_type', BuildingUnit::class)
                ->whereNot('payment_method', 'cash')
                ->where('created_at', '>', $start_date)
                ->get();

            $sum = 0;
            foreach ($ins as $in) {
                if (floor($in->id / 2) % 2 == 0) {
                    $sum += $in->amount;
                    if ($sum <= 61000000) {
                        $ids[] = $in->id;
                    }
                }
            }
            $invoices = $invoices->whereNot(function ($query) use ($ids) {
                $query->whereNot('payment_method', 'cash')
                    ->whereIn('id', $ids);
            });
        }

        if(auth()->buildingManager()->building->name_en == 'hshcomplex'){
            // $start_date = Carbon::parse('2024-05-25 12:45');
            // $days = 3;
            // $today = Carbon::now()->endofDay();
            // if ($today->diff($start_date)->days >= $days){
            //     $limit = $today->addDays(-1 * $days);
            // }else{
            //     $limit = $start_date;
            // }
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = $invoices->whereNot(function ($query) use ($ids) {
                $query->whereNot('payment_method', 'cash')
                ->whereNotIn('serviceable_id', $ids)
                // ->where('created_at', '>', $limit)
                ->whereBetween('created_at', [Carbon::parse('2024-05-25 12:45'), Carbon::parse('2024-05-26 08:00')]);
            });
        }

        if(auth()->buildingManager()->building->name_en == 'hshcomplex'){
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = $invoices->whereNot(function ($query) use ($ids) {
                $query->whereNot('payment_method', 'cash')
                ->whereNotIn('serviceable_id', $ids)
                // ->where('created_at', '>', Carbon::parse('2024-06-08 10:15'));
                ->whereBetween('created_at', [Carbon::parse('2024-06-08 10:15'), Carbon::parse('2024-06-08 23:59')]);
            });
        }

        if(auth()->buildingManager()->building->name_en == 'hshcomplex'){
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = $invoices->whereNot(function ($query) use ($ids) {
                $query->whereNot('payment_method', 'cash')
                ->whereNotIn('serviceable_id', $ids)
                // ->where('created_at', '>', Carbon::parse('2024-06-08 10:15'));
                ->whereBetween('created_at', [Carbon::parse('2024-06-15 16:00'), Carbon::parse('2024-06-15 23:59')]);
            });
        }

        if(auth()->buildingManager()->building->name_en == 'hshcomplex'){
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = $invoices->whereNot(function ($query) use ($ids) {
                $query->whereNot('payment_method', 'cash')
                ->whereNotIn('serviceable_id', $ids)
                // ->where('created_at', '>', Carbon::parse('2024-06-17 08:00'));
                ->whereBetween('created_at', [Carbon::parse('2024-06-17 08:00'), Carbon::parse('2024-06-19 08:00')]);
            });
        }

        if (auth()->buildingManager()->building->name_en == 'atishahr') {
            $start_date = Carbon::parse('2024-03-20 00:00');
            $ids = [];
            $ins = auth()->buildingManager()->building
                ->invoices()
                ->where('status', 'paid')
                ->where('is_verified', 1)
                ->where('serviceable_type', BuildingUnit::class)
                ->whereNot('payment_method', 'cash')
                ->where('created_at', '>', $start_date)
                ->get();

            $sum = 0;
            foreach ($ins as $in) {
                $sum += $in->amount;
                if ($sum <= 40000000) {
                    $ids[] = $in->id;
                }
            }
            $invoices = $invoices->whereNot(function ($query) use ($ids) {
                $query->whereNot('payment_method', 'cash')
                    ->whereIn('id', $ids);
            });
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

    public function pdf(Request $request)
    {
        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            return abort(500);
        }
        $invoices = $this->getInvoices($request);

        $pdf = Pdf::loadHTML(view('pdf.invoicePdf', [
            'invoices' => $invoices,
            'unit' => request()->unit ? BuildingUnit::find(request()->unit) : null,
            'request' => request()->all(),
            'start_date' => request()->start_date ? Jalalian::forge(request()->start_date)->format('Y/m/d') : null,
            'end_date' => request()->end_date ? Jalalian::forge(request()->end_date)->format('Y/m/d') : null,
        ]))->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function store(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'decimal:0,1', function ($attribute, $value, $fail) use ($request) {
                if ($value < 0) {
                    $fail(__("مبلغ وارد شده باید بزرگتر از صفر باشد"));
                }
            }],
            'description' => 'required|string',
            'date' => 'nullable|date',
            'type' => 'required|in:cost,income',
            'show_units' => 'nullable|boolean',
            'bank_id' => 'nullable|exists:accounting_details,id,building_id,' . auth()->buildingManager()->building->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoice = Invoice::create([
            'building_id' => $building->id,
            'amount' => $request->type == 'cost' ? -$request->amount : $request->amount,
            'description' => $request->description,
            'is_verified' => 1,
            'status' => 'paid',
            'payment_method' => 'cash',
            'serviceable_type' => Building::class,
            'serviceable_id' => $building->id,
            'created_at' => $request->date ?? now(),
            'show_units' => $request->show_units ?? 1,
            'bank_id' => $request->type == 'income' ? null : $request->bank_id ?? auth()->buildingManager()->building->accountingDetails()->where('type', 'bank')->first()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => __("فاکتور با موفقیت اضافه شد"),
        ], 201);
    }

    public function addMultiple(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:cost,income',
            'items' => 'required|array',
            'items.*.amount' => function ($attribute, $value, $fail) {
                [$group, $position, $name] = explode('.', $attribute);
                $position += 1;
                if (!Validator::make(['item' => $value], ['item' => 'required'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را وارد کنید");
                }

                if (!Validator::make(['item' => $value], ['item' => 'decimal:0,1'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }

                if ($value < 1) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }
            },
            'items.*.description' => 'required',
            'items.*.date' => 'nullable|date',
            'show_units' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::connection('mysql')->beginTransaction();

        try {
            foreach ($request->items as $item) {
                Invoice::create([
                    'building_id' => $building->id,
                    'amount' => $request->type == 'cost' ? -$item['amount'] : $item['amount'],
                    'is_verified' => 1,
                    'description' => $item['description'],
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'serviceable_type' => 'App\Models\Building',
                    'serviceable_id' => $building->id,
                    'created_at' => (isset($item['date']) && $item['date']) ? $item['date'] : Carbon::now(),
                    'updated_at' => (isset($item['date']) && $item['date']) ? $item['date'] : Carbon::now(),
                    'show_units' => $request->show_units ?? 1,
                    'bank_id' => $request->type == 'income' ? null : $request->bank_id ?? auth()->buildingManager()->building->accountingDetails()->where('type', 'bank')->first()->id,
                ]);
            }
            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }


        return response()->json([
            'success' => true,
            'message' => 'فاکتور های مورد نظر با موفقیت افزوده شد.',
        ], 201);
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => new InvoiceResource($invoice)
            ]
        ]);
    }

    public function receipt(Invoice $invoice)
    {
        if ($invoice->building_id != auth()->buildingManager()->building_id || $invoice->amount < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $pdf = Pdf::loadHTML(view('pdf.receipt', [
            'invoice' => $invoice,
        ]))->setPaper('a5', 'landscape');
        return $pdf->stream();
    }

    public function verify(Invoice $invoice)
    {
        if ($invoice->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'فاکتور قبلا تایید شده است.'
            ], 422);
        }

        $invoice->is_verified = 1;
        $invoice->save();

        $unit = $invoice->service;
        $unit->charge_debt = round($unit->charge_debt - $invoice->amount, 1);
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت تایید شد.'
        ]);
    }

    public function reject(Invoice $invoice)
    {
        if ($invoice->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'فاکتور قبلا تایید شده است.'
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت حذف شد.'
        ]);
    }

    public function update(Invoice $invoice, Request $request)
    {
        if ($invoice->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->payment_method !== 'cash') {
            return response()->json([
                'success' => false,
                'message' => 'امکان ویرایش فاکتور های پرداخت شده وجود ندارد.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'decimal:0,1', function ($attribute, $value, $fail) use ($request) {
                if ($value == 0) {
                    $fail(__("مبلغ وارد شده باید کمتر یا بزرگتر از صفر باشد"));
                }
            }],
            'description' => 'nullable|string',
            'show_units' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($invoice->serviceable_type == Building::class) {
            $invoice->amount = $invoice->amount > 0 ? $request->amount : -$request->amount;
            $invoice->description = $request->description;
            $invoice->show_units = $request->show_units ?? 1;
            $invoice->save();

            return response()->json([
                'success' => true,
                'message' => 'فاکتور با موفقیت ویرایش شد.'
            ]);
        }

        DB::connection('mysql')->beginTransaction();

        try {
            $service = $invoice->service()->withTrashed()->first();
            if ($invoice->is_verified && $service && $invoice->serviceable_type == BuildingUnit::class) {
                // $service->increment('charge_debt', $invoice->amount);
                $service->charge_debt = round($service->charge_debt + $invoice->amount, 1);
                $service->save();
            }
            $invoice->delete();



            $amount = $invoice->amount > 0 ? $request->amount : -$request->amount;

            // $invoice->amount = $amount;
            // $invoice->description = $request->description;
            // $invoice->save();

            // if ($invoice->is_verified && $service) {
            //     // $service->increment('charge_debt', -$amount);
            //     $service->charge_debt = round($service->charge_debt - $amount, 1);
            //     $service->save();
            // }

            $new_invoice = Invoice::create([
                'user_id' => $invoice->user_id,
                'building_id' => $invoice->building_id,
                'debt_type_id' => $invoice->debt_type_id,
                'amount' => $amount,
                'discount_code_id' => $invoice->discount_code_id,
                'status' => $invoice->status,
                'payment_method' => $invoice->payment_method,
                'payment_id' => $invoice->payment_id,
                'payment_card_number' => $invoice->payment_card_number,
                'payment_tracenumber' => $invoice->payment_tracenumber,
                'description' => $request->description,
                'resident_type' => $invoice->resident_type,
                'serviceable_id' => $invoice->serviceable_id,
                'serviceable_type' => $invoice->serviceable_type,
                'is_verified' => $invoice->is_verified,
                'fine_exception' => $invoice->fine_exception,
                'created_at' => $invoice->created_at,
                'data' => $invoice->data,
                'bank_id' => $invoice->bank_id,
                'early_discount_until' => $invoice->early_discount_until,
                'early_discount_amount' => $invoice->early_discount_amount,
            ]);

            if ($new_invoice->is_verified && $service && $invoice->serviceable_type == BuildingUnit::class) {
                // $service->increment('charge_debt', $amount);
                $service->charge_debt = round($service->charge_debt - $amount, 1);
                $service->save();
            }

            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در ویرایش فاکتور رخ داده است.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت ویرایش شد.'
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->payment_method !== 'cash') {
            return response()->json([
                'success' => false,
                'message' => 'امکان حذف فاکتور های پرداخت شده وجود ندارد.'
            ], 422);
        }

        DB::connection('mysql')->beginTransaction();

        try {
            $service = $invoice->service()->withTrashed()->first();
            if ($invoice->is_verified && $invoice->serviceable_type == BuildingUnit::class) {
                // $service->increment('charge_debt', $invoice->amount);
                $service->charge_debt = round($service->charge_debt + $invoice->amount, 1);
                $service->save();
            }
            $invoice->delete();
            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در حذف فاکتور رخ داده است.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت حذف شد.'
        ]);
    }


    public function multipleDestroy(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'ids' => 'array|required',
            'ids.*' => 'numeric|exists:invoices,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->ids as $id) {
            $invoice = Invoice::find($id);
            if ($invoice->building_id != auth()->buildingManager()->building_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            if ($invoice->payment_method !== 'cash') {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان حذف فاکتور های پرداخت شده وجود ندارد.'
                ], 422);
            }

            DB::connection('mysql')->beginTransaction();

            try {
                $service = $invoice->service()->withTrashed()->first();
                if ($invoice->is_verified && $invoice->serviceable_type == BuildingUnit::class) {
                    // $service->increment('charge_debt', $invoice->amount);
                    $service->charge_debt = round($service->charge_debt + $invoice->amount, 1);
                    $service->save();
                }
                $invoice->delete();
                DB::connection('mysql')->commit();
            } catch (\Exception $e) {
                DB::connection('mysql')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'خطایی در حذف فاکتور رخ داده است.'
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت حذف شد.'
        ]);
    }
}
