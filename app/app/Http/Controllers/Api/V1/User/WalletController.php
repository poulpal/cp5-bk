<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Facades\CommissionHelper;
use App\Helpers\Inopay;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\InvoiceResource;
use App\Models\Commission;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice as InvoiceFacade;
use Shetabit\Payment\Facade\Payment;


class WalletController extends Controller
{
    public function transactions()
    {
        $invoices = Invoice::where(function ($query) {
            $query->where('serviceable_type', 'wallet')
                ->orWhere('payment_method', 'wallet');
        })
            ->where('user_id', auth()->user()->id)
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($invoices as $invoice) {
            if ($invoice->payment_method == 'wallet') {
                $invoice->amount = $invoice->amount * -1;
            }
        }

        $balance = auth()->user()->balance;

        if (config('app.type') === 'kaino') {
            $inopay = new Inopay();
            $balance = $inopay->getBalance(auth()->user());
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => round($balance, 1),
                'transactions' => InvoiceResource::collection($invoices),
            ]
        ]);
    }

    public function balance()
    {
        if (config('app.type') === 'kaino') {
            $inopay = new Inopay();
            $balance = $inopay->getBalance(auth()->user());
            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => round($balance, 1),
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'balance' => round(auth()->user()->balance, 1),
            ]
        ]);
    }

    public function addBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|decimal:0,1|min:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $units = auth()->user()->building_units;
        foreach ($units as $unit) {
            if ($unit->building->name_en == 'hshcomplex' || $unit->building->name_en == 'atishahr' || $unit->building->name_en == 'afra' || $unit->building->id == 224) {
                return response()->json([
                    'success' => false,
                    'message' => __("امکان افزایش موجودی کیف پول ساختمان شما وجود ندارد"),
                ], 403);
            }
        }

        if (config('app.type') === 'kaino') {
            $payment_invoice = (new InvoiceFacade)->amount($request->amount)->detail([
                'mobile' => auth()->user()->mobile,
                'account' => auth()->user(),
            ]);

            $payment = Payment::purchase(
                $payment_invoice,
                function ($driver, $transactionId) use ($request, $payment_invoice) {
                    $invoice = auth()->user()->invoices()->create([
                        'user_id' => auth()->user()->id,
                        'payment_id' => $transactionId,
                        'payment_method' => get_class($driver),
                        'amount' => $request->amount,
                        'serviceable_type' => 'wallet',
                        'description' => __("افزایش موجودی کیف پول"),
                    ]);
                }
            )->pay()->toJson();

            return response()->json([
                'success' => true,
                'data' => [
                    'driver' => config('payment.default'),
                    'redirect' => json_decode($payment),
                    'redirectUrl' => route('paymentRedirect', [
                        'method' => json_decode($payment)->method,
                        'action' => json_decode($payment)->action,
                        'inputs' => json_encode(json_decode($payment)->inputs),
                    ]),
                    'callback' => route('v1.callback.' . config('payment.default')),
                ]
            ]);
        }

        $commission_amount = 3900;
        // $commission_amount = 0;
        $payment_invoice = (new InvoiceFacade)->amount($request->amount + $commission_amount)->detail([
            'mobile' => auth()->user()->mobile,
            'business' => "CHARGEPAL - " . __("کیف پول"),
        ]);

        $payment = Payment::purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($request, $commission_amount, $payment_invoice) {
                $invoice = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $request->amount,
                    'serviceable_type' => 'wallet',
                    'description' => __("افزایش موجودی کیف پول"),
                ]);

                $commission = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $commission_amount,
                    'serviceable_type' => Commission::class,
                    'description' => __("کمیسیون افزایش موجودی کیف پول"),
                ]);
            }
        )->pay()->toJson();



        return response()->json([
            'success' => true,
            'data' => [
                'driver' => config('payment.default'),
                'redirect' => json_decode($payment),
                'redirectUrl' => route('paymentRedirect', [
                    'method' => json_decode($payment)->method,
                    'action' => json_decode($payment)->action,
                    'inputs' => json_encode(json_decode($payment)->inputs),
                ]),
                'callback' => route('v1.callback.' . config('payment.default')),
            ]
        ]);
    }
}
