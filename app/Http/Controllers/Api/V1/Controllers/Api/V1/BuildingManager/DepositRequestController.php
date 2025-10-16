<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Helpers\Inopay;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\DepositRequestResource;
use App\Mail\CustomMail;
use App\Models\DepositRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DepositRequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show', 'pdf']);
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        $depositRequests = auth()->buildingManager()->building->depositRequests();

        if (request()->has('sort') && request()->sort) {
            $depositRequests = $depositRequests->orderBy(request()->sort, request()->order ?? 'desc');
        } else {
            $depositRequests = $depositRequests->orderBy('created_at', 'desc');
        }

        if (request()->has('paginate') && request()->paginate) {
            $depositRequests = $depositRequests->paginate(request()->perPage ?? 20);
        } else {
            $depositRequests = $depositRequests->get();
        }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($depositRequests, DepositRequestResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => DepositRequestResource::collection($depositRequests),
                ]
            ]);
        }
    }

    public function pdf(Request $request)
    {
        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            return abort(500);
        }
        $validator = Validator::make(request()->all(), [
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        $depositRequests = auth()->buildingManager()->building->depositRequests();

        if (request()->has('sort') && request()->sort) {
            $depositRequests = $depositRequests->orderBy(request()->sort, request()->order ?? 'desc');
        } else {
            $depositRequests = $depositRequests->orderBy('created_at', 'desc');
        }

        if (request()->has('paginate') && request()->paginate) {
            $depositRequests = $depositRequests->paginate(request()->perPage ?? 20);
        } else {
            $depositRequests = $depositRequests->get();
        }

        $pdf = Pdf::loadHTML(view('pdf.depositRequestPdf', [
            'depositRequests' => $depositRequests,
        ]))->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:500000',
            'deposit_to' => 'required|in:me,other',
            'sheba' => 'required_if:deposit_to,other|nullable',
            'name' => 'required_if:deposit_to,other|nullable',
            'description' => 'required|string',
            'balance_id' => 'nullable|exists:balances,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $building = auth()->buildingManager()->building;
        $details = auth()->buildingManager()->details;

        if (config('app.type') === 'kaino') {
            $inopay = new Inopay();
            $balance = $inopay->getBalance($building);
        } else {
            $balance = $building->balance;
        }

        if (!auth()->buildingManager()->building->signed_contract){
            if($request->expectsJson()){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'contract' => 'برای دسترسی به این امکان، ابتدا قرارداد ساختمان را امضا کنید.'
                    ],
                    'action' => 'sign_contract'
                ], 403);
            }
        }

        if (($request->amount / 10) > $balance) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => 'مبلغ درخواستی از موجودی حساب شما بیشتر است.'
                ]
            ], 422);
        }

        if ($request->balance_id) {
            $balance = $building->balances()->find($request->balance_id);
            if (!$balance) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'balance_id' => 'صندوق انتخابی یافت نشد.'
                    ]
                ], 422);
            }
            if ($balance->amount < ($request->amount / 10)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'amount' => 'مبلغ درخواستی از موجودی صندوق انتخابی بیشتر است.'
                    ]
                ], 422);
            }
        }

        if ($request->deposit_to == 'me' && $request->has('sheba') && $request->sheba != $details->sheba_number) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'sheba' => 'شماره شبا وارد شده با شماره شبای حساب شما مطابقت ندارد.'
                ]
            ], 422);
        }

        $pending_requests = $building->depositRequests()->where('status', 'pending')->get();
        if ($pending_requests->count() > 0) {
            return response()->json([
                'success' => false,
                'error' => 'شما در حال حاضر یک درخواست واریز در انتظار پاسخ مدیریت هستید.'
            ], 400);
        }

        $depositRequest = null;

        DB::transaction(function () use ($request, $building, &$depositRequest) {

            $depositRequest = $building->depositRequests()->create([
                'amount' => ($request->amount / 10),
                'description' => $request->description,
                'status' => 'pending',
                'deposit_to' => $request->deposit_to,
                'sheba' => $request->sheba . ' - ' . $request->name,
                'balance_id' => $request->balance_id ?? null,
            ]);

            if (config('app.type') === 'kaino') {
                $inopay = new Inopay();
                try {
                    $data = $inopay->paymentOrder($request->amount / 10, $building, $request->sheba, $request->name, $request->description);
                    $depositRequest->data = $data;
                    $depositRequest->description = $request->description . ' - شماره پیگیری ' . $data['paymentReference'];
                    $depositRequest->status = 'accepted';
                    $depositRequest->save();
                } catch (\Exception $e) {
                    throw new \Exception('خطا در ارسال درخواست واریز. لطفا دوباره تلاش کنید.');
                }
            }
        });

        Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'poulpal33@poulpal.com'])->send(
            new CustomMail(
                'CHARGEPAL - درخواست واریز جدید',
                "یک درخواست واریز جدید ثبت شد. <br>
                مبلغ: " . number_format($request->amount) . " تومان <br>
                توضیحات: " . $request->description . "<br>
                شماره شبا: " . $request->sheba . "<br>
                نام صاحب حساب: " . $request->name . "<br>
                ساختمان: " . $building->name . "<br>"
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'درخواست واریز با موفقیت ثبت شد.',
            'data' => [
                'depositRequest' => DepositRequestResource::make($depositRequest),
            ]
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DepositRequest  $depositRequest
     * @return \Illuminate\Http\Response
     */
    public function show(DepositRequest $depositRequest)
    {
        if ($depositRequest->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'errors' => ['درخواست واریز مورد نظر یافت نشد.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'depositRequest' => DepositRequestResource::make($depositRequest),
            ]
        ]);
    }
}
