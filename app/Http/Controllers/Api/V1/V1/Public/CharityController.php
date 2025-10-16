<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Facades\CommissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\InvoiceResource;
use App\Models\Commission;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice as InvoiceFacade;
use Shetabit\Payment\Facade\Payment;


class CharityController extends Controller
{
    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|decimal:0,1|min:5000',
            'mobile' => 'required|numeric|digits:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (config('app.type') === 'kaino') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'modules' => __("این امکان در این نسخه وجود ندارد"),
                ]
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) {
            $user = User::create([
                'mobile' => $request->mobile,
            ]);
        }

        $commission_amount = 0;
        $payment_invoice = (new InvoiceFacade)->amount($request->amount + $commission_amount)->detail([
            'mobile' => $user->mobile,
            'business' => "CHARGEPAL - " . __("خیریه"),
        ]);

        $payment = Payment::purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($request, $commission_amount, $payment_invoice, $user) {
                $invoice = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $request->amount,
                    'serviceable_type' => 'charity',
                    'description' => __("خیریه"),
                ]);

                $commission = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $commission_amount,
                    'serviceable_type' => Commission::class,
                    'description' => __("کمیسیون پرداخت برای خیریه"),
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
