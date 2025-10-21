<?php

namespace App\Http\Controllers;

use App\Facades\CommissionHelper;
use App\Mail\CustomMail;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\Invoice;
use App\Models\Module;
use App\Models\PendingDeposit;
use App\Models\Plan;
use App\Models\Poulpal\PoulpalBusiness;
use App\Models\Poulpal\PoulpalFactor;
use App\Models\Poulpal\PoulpalInvoice;
use App\Models\Poulpal\PoulpalUser;
use App\Models\Reservation;
use App\Models\SmsMessage;
use App\Models\Toll;
use App\Notifications\BuildingManager\UserPaidCharge;
use App\Notifications\BuildingManager\UserReserved;
use App\Notifications\User\PaidCharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

class CallbackController extends Controller
{
    protected $frontendCallbackUrl;

    public function __construct()
    {
        // تنظیم URL بر اساس محیط
        if (config('app.env') == 'production' || config('app.env') == 'test') {
            $this->frontendCallbackUrl = 'https://cp.chargepal.ir/paymentStatus';
            
            // TODO: Change payment status page for kaino
            // if (config('app.type') == 'kaino') {
            //     $this->frontendCallbackUrl = 'https://digisign.chargepal.ir/paymentStatus';
            // }
        } else {
            $this->frontendCallbackUrl = 'http://localhost:3000/paymentStatus';
        }
    }

    public function paymentRedirect(Request $request)
    {
        return view('shetabitPayment::redirectForm', [
            'method' => $request->method,
            'action' => $request->action,
            'inputs' => json_decode($request->inputs),
        ]);
    }

    public function showPaymentStatus()
    {
        return view('paymentStatus');
    }

    public function test(Request $request)
    {
        if (config('app.env') == 'production') {
            return abort(400);
        }
        $transaction_id = $request->transactionId;
        try {

            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->first();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->first();
            $receipt = Payment::amount($invoice->amount)->transactionId($transaction_id)->verify();
            $this->handleInvoice($invoice, $commission);
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => true,
                    'amount' => $invoice->amount + $commission?->amount,
                    'tracenumber' => $receipt->getReferenceId(),
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد.',
            ]);
        } catch (InvalidPaymentException $exception) {
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function inopay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'result' => 'required',
            'identifier' => 'required',
            'referenceNumber' => 'nullable',
            'responseText' => 'nullable',
            'sign' => 'required',
            'localDate' => 'nullable',
            'voucherReference' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction_id = $request->identifier;
        $response = $request->result;
        $tracenumber = $request->referenceNumber;
        try {
            if ($response != 'true') {
                throw new InvalidPaymentException('پرداخت ناموفق بود.');
            }
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->first();
            $invoice->payment_tracenumber = $tracenumber ?? null;
            $invoice->payment_response = json_encode($request->all());
            $invoice->save();
            if ($commission) {
                $commission->payment_tracenumber = $tracenumber ?? null;
                $commission->payment_response = json_encode($request->all());
                $commission->save();
            }
            if ($invoice->status == 'paid') {
                throw new InvalidPaymentException('این فاکتور قبلا پرداخت شده است.');
            }
            $receipt = Payment::amount($invoice->amount + $commission?->amount)->transactionId($transaction_id)->verify();
            $cardnumber = $receipt->getDetail('maskedPan');
            if ($cardnumber) {
                $invoice->payment_card_number = $cardnumber;
                $invoice->saveQuietly();
            }
            try {
                $this->handleInvoice($invoice, $commission);
            } catch (\Exception $exception) {
                Mail::to('cc2com.com@gmail.com')->send(new CustomMail('خطا در پردازش تراکنش', $exception->getMessage()));
                throw new InvalidPaymentException($exception->getMessage());
            }
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => true,
                    'amount' => $invoice->amount + $commission?->amount,
                    'tracenumber' => $receipt->getReferenceId(),
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد.',
            ]);
        } catch (InvalidPaymentException $exception) {
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->first();
            $invoice->description = $invoice->description . "-" . $exception->getMessage();
            $invoice->payment_response = json_encode($request->all());
            $invoice->save();

            if ($commission) {
                $commission->description = $commission->description . "-" . $exception->getMessage();
                $commission->payment_response = json_encode($request->all());
                $commission->save();
            }

            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function sepehr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'respcode' => 'required',
            'respmsg' => 'required',
            'amount' => 'required|integer',
            'invoiceid' => 'required',
            'payload' => 'nullable',
            'terminalid' => 'nullable',
            'tracenumber' => 'nullable',
            'rrn' => 'nullable',
            'datePaid' => 'nullable',
            'issuerbank' => 'nullable',
            'cardnumber' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction_id = $request->invoiceid;
        $response = $request->respcode;
        $respmsg = $request->respmsg;
        $amount = (int)$request->amount / 10;
        $tracenumber = $request->tracenumber;
        $cardnumber = $request->cardnumber;
        try {
            if ($response != 0) {
                throw new InvalidPaymentException($respmsg ?? 'پرداخت ناموفق بود.');
            }
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->firstOrFail();
            $invoice->payment_card_number = $cardnumber ?? null;
            $invoice->payment_tracenumber = $tracenumber ?? null;
            $invoice->payment_response = json_encode($request->all());
            $invoice->save();
            $commission->payment_card_number = $cardnumber ?? null;
            $commission->payment_tracenumber = $tracenumber ?? null;
            $commission->payment_response = json_encode($request->all());
            $commission->save();
            if ($invoice->status == 'paid') {
                throw new InvalidPaymentException('این فاکتور قبلا پرداخت شده است.');
            }
            $receipt = Payment::config(['terminalId' => $request->terminalid])->amount($amount)->transactionId($transaction_id)->verify();
            try {
                $this->handleInvoice($invoice, $commission);
            } catch (\Exception $exception) {
                Mail::to('cc2com.com@gmail.com')->send(new CustomMail('خطا در پردازش تراکنش', $exception->getMessage()));
                throw new InvalidPaymentException($exception->getMessage());
            }
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => true,
                    'amount' => $invoice->amount + $commission?->amount,
                    // 'tracenumber' => $receipt->getReferenceId(),
                    'tracenumber' => $tracenumber,
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد.',
                'data' => [
                    'amount' => $invoice->amount + $commission->amount,
                    'tracenumber' => $request->tracenumber,
                ]
            ]);
        } catch (InvalidPaymentException $exception) {
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->firstOrFail();
            // $invoice->description = $invoice->description . "-" . $exception->getMessage();
            $commission->description = $commission->description . "-" . $exception->getMessage();

            $invoice->payment_response = json_encode($request->all());
            $commission->payment_response = json_encode($request->all());

            $invoice->save();
            $commission->save();
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function sep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "MID" => 'required',
            "TerminalId" => 'required',
            "RefNum" => 'nullable',
            "ResNum" => 'required',
            "State" => 'required',
            "TraceNo" => 'nullable',
            "Amount" => 'required',
            "Wage" => 'nullable',
            "Rrn" => 'nullable',
            "SecurePan" => 'nullable',
            "Status" => 'required',
            "Token" => 'required',
            "HashedCardNumber" => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction_id = $request->ResNum;
        $response = $request->Status;
        $respmsg = $request->State;
        $amount = (int)$request->Amount / 10;
        $tracenumber = $request->TraceNo;
        $cardnumber = $request->SecurePan;
        try {
            if ($response != 2) {
                throw new InvalidPaymentException($respmsg ?? 'پرداخت ناموفق بود.');
            }
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->firstOrFail();
            $invoice->payment_card_number = $cardnumber ?? null;
            $invoice->payment_tracenumber = $tracenumber ?? null;
            $invoice->payment_response = json_encode($request->all());
            $invoice->save();
            $commission->payment_card_number = $cardnumber ?? null;
            $commission->payment_tracenumber = $tracenumber ?? null;
            $commission->payment_response = json_encode($request->all());
            $commission->save();
            if ($invoice->status == 'paid') {
                throw new InvalidPaymentException('این فاکتور قبلا پرداخت شده است.');
            }
            $receipt = Payment::config(['terminalId' => $request->TerminalId])->amount($invoice->amount + $commission->amount)->transactionId($transaction_id)->verify();
            try {
                $this->handleInvoice($invoice, $commission);
            } catch (\Exception $exception) {
                Mail::to('cc2com.com@gmail.com')->send(new CustomMail('خطا در پردازش تراکنش', $exception->getMessage()));
                throw new InvalidPaymentException($exception->getMessage());
            }
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => true,
                    'amount' => $invoice->amount + $commission?->amount,
                    // 'tracenumber' => $receipt->getReferenceId(),
                    'tracenumber' => $tracenumber,
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد.',
                'data' => [
                    'amount' => $invoice->amount + $commission->amount,
                    'tracenumber' => $request->tracenumber,
                ]
            ]);
        } catch (InvalidPaymentException $exception) {
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->firstOrFail();
            // $invoice->description = $invoice->description . "-" . $exception->getMessage();
            $commission->description = $commission->description . "-" . $exception->getMessage();

            $invoice->payment_response = json_encode($request->all());
            $commission->payment_response = json_encode($request->all());

            $invoice->save();
            $commission->save();
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function pasargad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "status" => 'required',
            "invoiceId" => 'required',
            "referenceNumber" => 'nullable',
            "trackId" => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction_id = $request->invoiceId;
        $response = $request->status;
        $tracenumber = $request->referenceNumber;
        try {
            if ($response != 'success') {
                throw new InvalidPaymentException('پرداخت ناموفق بود.');
            }
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->whereNot('payment_method', 'cash')->firstOrFail();
            // $invoice->payment_card_number = $cardnumber ?? null;
            $invoice->payment_tracenumber = $tracenumber ?? null;
            $invoice->payment_response = json_encode($request->all());
            $invoice->save();
            // $commission->payment_card_number = $cardnumber ?? null;
            $commission->payment_tracenumber = $tracenumber ?? null;
            $commission->payment_response = json_encode($request->all());
            $commission->save();
            if ($invoice->status == 'paid') {
                throw new InvalidPaymentException('این فاکتور قبلا پرداخت شده است.');
            }
            if (isset($invoice->data->terminal_id)) {
                switch ($invoice->data->terminal_id) {
                    case '77041334':
                        $payment = Payment::config([
                            'username' => 'ERP77049230',
                            'password' => 'VcbiKh!rr2',
                            'merchantId' => '77049230',
                            'terminalCode' => '77041334',
                        ]);
                        break;
                    default:
                        $payment = Payment::config([]);
                        break;
                }
            } else {
                $payment = Payment::config([]);
            }
            $receipt = $payment->amount($invoice->amount + $commission->amount)->transactionId($transaction_id)->verify();
            // $cardnumber = $receipt->getDetail('maskedCardNumber');
            // $invoice->payment_card_number = $cardnumber;
            // $invoice->save();
            // $commission->payment_card_number = $cardnumber;
            // $commission->save();
            try {
                $this->handleInvoice($invoice, $commission);
            } catch (\Exception $exception) {
                Mail::to('cc2com.com@gmail.com')->send(new CustomMail('خطا در پردازش تراکنش', $exception->getMessage()));
                throw new InvalidPaymentException($exception->getMessage());
            }
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => true,
                    'amount' => $invoice->amount + $commission?->amount,
                    // 'tracenumber' => $receipt->getReferenceId(),
                    'tracenumber' => $tracenumber,
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد.',
                'data' => [
                    'amount' => $invoice->amount + $commission->amount,
                    'tracenumber' => $request->tracenumber,
                ]
            ]);
        } catch (InvalidPaymentException $exception) {
            $invoice = Invoice::where('payment_id', $transaction_id)->whereNot('serviceable_type', Commission::class)->firstOrFail();
            $commission = Invoice::where('payment_id', $transaction_id)->where('serviceable_type', Commission::class)->firstOrFail();
            // $invoice->description = $invoice->description . "-" . $exception->getMessage();
            $commission->description = $commission->description . "-" . $exception->getMessage();

            $invoice->payment_response = json_encode($request->all());
            $commission->payment_response = json_encode($request->all());

            $invoice->save();
            $commission->save();
            if (!$request->wantsJson()) {
                $query = http_build_query([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
                return redirect()->to($this->frontendCallbackUrl . '?' . $query);
            }
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function handleInvoice($invoice, $commission)
    {
        DB::transaction(function () use ($invoice, $commission) {
            if ($invoice->serviceable_type == BuildingUnit::class) {
                $invoice->status = 'paid';
                $invoice->save();
                $unit = $invoice->service;
                $unit->charge_debt = round($unit->charge_debt - $invoice->amount, 1);
                $unit->save();

                $discount_invoice = Invoice::where('payment_id', $invoice->payment_id)->where('payment_method', 'cash')->first();
                if ($discount_invoice) {
                    $discount_invoice->status = 'paid';
                    $discount_invoice->save();
                    $unit->charge_debt = round($unit->charge_debt - $discount_invoice->amount, 1);
                    $unit->save();

                    $unit->invoices()->where('early_discount_until', '>=', $invoice->created_at)->update([
                        'early_discount_amount' => 0,
                    ]);
                }

                if ($unit->building->options->separate_owner_payment_balance && $invoice->resident_type == 'owner') {
                    $unit->building->toll_balance = round($unit->building->toll_balance + $invoice->amount, 1);
                    $unit->building->save();
                } else {
                    $unit->building->balance = round($unit->building->balance + $invoice->amount, 1);
                    $unit->building->save();
                }

                if ($unit->building->options->multi_balance) {
                    if ($unit->balance) {
                        $unit->balance->amount = round($unit->balance->amount + $invoice->amount, 1);
                        $unit->balance->save();
                    }
                }

                if ($unit->building->options->send_building_manager_payment_notification) {
                    foreach ($unit->building->mainBuildingManagers as $manager) {
                        $manager->notify(new UserPaidCharge($invoice->amount, $invoice->user->full_name, $invoice->user->mobile, $invoice->payment_tracenumber ?? '', $unit->unit_number));
                    }
                }

                $invoice->user->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                $commission->status = 'paid';
                $commission->save();

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'پرداخت شارژ - ساختمان : ' . $unit->building->name . " - " . $invoice->payment_tracenumber ?? "",
                        "نام ساختمان : " . $unit->building->name . "<br>" .
                            "واحد : " . $unit->unit_number . " - " . $invoice->user->mobile . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format($commission->amount * 10) . " ریال" . "<br>"
                    )
                );

                $pending_deposit = new PendingDeposit();
                $pending_deposit->invoice()->associate($invoice);
                $pending_deposit->building()->associate($unit->building);
                $pending_deposit->save();
            }
            if ($invoice->serviceable_type == 'wallet') {
                $invoice->status = 'paid';
                $invoice->save();

                $invoice->user->balance = round($invoice->user->balance + $invoice->amount, 1);
                $invoice->user->save();

                $invoice->user->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                if ($commission) {
                    $commission->status = 'paid';
                    $commission->save();
                }

                $units_string = '';
                foreach ($invoice->user->building_units as $unit) {
                    $units_string .= 'واحد ' . $unit->unit_number . " - ساختمان:" . $unit->building->name . "<br>";
                }

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'افزایش موجودی کیف پول - ' . $invoice->payment_tracenumber ?? "",
                        "نام کاربر : " . $invoice->user->full_name . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format(($commission ? $commission->amount : 0) * 10) . " ریال" . "<br>" .
                            "واحدهای کاربر : " . $units_string . "<br>"
                    )
                );
            }
            if ($invoice->serviceable_type == Reservation::class) {
                $invoice->status = 'paid';
                $invoice->save();
                $reservation = $invoice->service;
                $reservation->status = 'paid';
                $reservation->save();

                $reservation->reservable->building->balance = round($reservation->reservable->building->balance + $invoice->amount, 1);
                $reservation->reservable->building->save();

                $invoice->user->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                if ($reservation->reservable->building->options->send_building_manager_payment_notification) {
                    foreach ($reservation->reservable->building->mainBuildingManagers as $manager) {
                        $manager->notify(new UserReserved($invoice->amount, $invoice->user->full_name, $invoice->user->mobile, $invoice->payment_tracenumber ?? '', $reservation->reservable->title));
                    }
                }

                if ($commission) {
                    $commission->status = 'paid';
                    $commission->save();
                }

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'پرداخت رزرو - ساختمان : ' . $reservation->reservable->building->name . " - " . $invoice->payment_tracenumber ?? "",
                        "نام ساختمان : " . $reservation->reservable->building->name . "<br>" .
                            "کاربر : " . $reservation->user->full_name . " - " . $reservation->user->mobile . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format(($commission ? $commission->amount : 0) * 10) . " ریال" . "<br>"
                    )
                );

                $pending_deposit = new PendingDeposit();
                $pending_deposit->invoice()->associate($invoice);
                $pending_deposit->building()->associate($reservation->reservable->building);
                $pending_deposit->save();
            }
            if ($invoice->serviceable_type == Toll::class) {
                $invoice->status = 'paid';
                $invoice->save();
                $toll = $invoice->service;
                $toll->status = 'paid';
                $toll->user_id = $invoice->user_id;
                $toll->save();

                if ($toll->building->options->separate_owner_payment_balance && $invoice->resident_type == 'owner') {
                    $toll->building->toll_balance = round($toll->building->toll_balance + $invoice->amount, 1);
                    $toll->building->save();
                } else {
                    $toll->building->balance = round($toll->building->balance + $invoice->amount, 1);
                    $toll->building->save();
                }

                if ($toll->building->options->send_building_manager_payment_notification) {
                    foreach ($toll->building->mainBuildingManagers as $manager) {
                        $manager->notify(new UserPaidCharge($invoice->amount, $invoice->user->full_name, $invoice->user->mobile, $invoice->payment_tracenumber ?? '', $toll->unit->unit_number));
                    }
                }

                $invoice->user->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                if ($commission) {
                    $commission->status = 'paid';
                    $commission->save();
                }

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'پرداخت عوارض - ساختمان : ' . $toll->unit->building->name . " - " . $invoice->payment_tracenumber ?? "",
                        "نام ساختمان : " . $toll->unit->building->name . "<br>" .
                            "واحد : " . $toll->unit->unit_number . " - " . $invoice->user->mobile . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format(($commission ? $commission->amount : 0) * 10) . " ریال" . "<br>"
                    )
                );

                // $pending_deposit = new PendingDeposit();
                // $pending_deposit->invoice()->associate($invoice);
                // $pending_deposit->building()->associate($toll->unit->building);
                // $pending_deposit->save();
            }
            if ($invoice->serviceable_type == Plan::class) {
                $invoice->status = 'paid';
                $invoice->save();
                $plan = $invoice->service;

                if ($invoice->discount_code) {
                    $invoice->discount_code->usage += 1;
                    $invoice->discount_code->save();
                }

                $building = $invoice->building;
                $building->plan_slug = $plan->slug;
                $building->plan_expires_at = now()->addMonths($invoice->data->duration)->endOfDay();
                $building->plan_duration = $building->plan_expires_at->diffInDays(now());
                $building->save();

                $invoice->building->mainBuildingManagers->first()->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                if ($commission) {
                    $commission->status = 'paid';
                    $commission->save();
                }

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'خرید پلن - ساختمان : ' . $invoice->building->name . " - " . $invoice->payment_tracenumber ?? "",
                        "نام ساختمان : " . $invoice->building->name . "<br>" .
                            "توضیحات : " . $invoice->description . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format(($commission ? $commission->amount : 0) * 10) . " ریال" . "<br>" .
                            "کد تخفیف : " . ($invoice->discount_code ? $invoice->discount_code->code : "بدون کد تخفیف") . "<br>"
                    )
                );
            }
            if ($invoice->serviceable_type == Module::class) {
                $invoice->status = 'paid';
                $invoice->save();

                if ($invoice->discount_code) {
                    $invoice->discount_code->usage += 1;
                    $invoice->discount_code->save();
                }

                $modules_slug = $invoice->data->modules;
                $modules = Module::whereIn('slug', $modules_slug)->get();
                $activeModules = $invoice->building->modules;

                foreach ($modules as $module) {
                    if ($activeModules->contains($module)) {
                        $module->extra_days = Carbon::parse($activeModules->where('slug', $module->slug)->first()->pivot->ends_at)->diffInDays(now());
                        $activeModules->where('slug', $module->slug)->first()->pivot->update(['ends_at' => now()]);
                    }
                    if ($module->type == 'base' && $activeModules->where('type', 'base')->count() > 0) {
                        foreach ($activeModules->where('type', 'base') as $activeModule) {
                            $activeModule->pivot->update(['ends_at' => now()]);
                        }
                    }
                    $invoice->building->modules()->attach($module->slug, [
                        'starts_at' => now(),
                        'ends_at' => now()->addYears(1)->addDays($module->extra_days ?? 0),
                        'price' => $module->price
                    ]);
                }

                $invoice->building->mainBuildingManagers->first()->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                if ($commission) {
                    $commission->status = 'paid';
                    $commission->save();
                }

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'خرید ماژول - ساختمان : ' . $invoice->building->name . " - " . $invoice->payment_tracenumber ?? "",
                        "نام ساختمان : " . $invoice->building->name . "<br>" .
                            "توضیحات : " . $invoice->description . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format(($commission ? $commission->amount : 0) * 10) . " ریال" . "<br>" .
                            "کد تخفیف : " . ($invoice->discount_code ? $invoice->discount_code->code : "بدون کد تخفیف") . "<br>"
                    )
                );
            }
            if ($invoice->serviceable_type == SmsMessage::class) {
                $invoice->status = 'paid';
                $invoice->save();

                $sms_price = CommissionHelper::getSmsPrice();
                $added_sms = floor($invoice->amount / $sms_price);

                $building = $invoice->building;
                $building->sms_balance = $building->sms_balance + $added_sms;
                $building->save();

                $invoice->building->mainBuildingManagers->first()->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                if ($commission) {
                    $commission->status = 'paid';
                    $commission->save();
                }

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'شارژ SMS - ساختمان : ' . $invoice->building->name . " - " . $invoice->payment_tracenumber ?? "",
                        "نام ساختمان : " . $invoice->building->name . "<br>" .
                            "توضیحات : " . $invoice->description . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format(($commission ? $commission->amount : 0) * 10) . " ریال" . "<br>"
                    )
                );
            }
            if ($invoice->serviceable_type == 'charity') {
                $invoice->status = 'paid';
                $invoice->save();

                $invoice->user->notify(new PaidCharge($invoice->amount, $invoice->payment_tracenumber ?? ''));

                if ($commission) {
                    $commission->status = 'paid';
                    $commission->save();
                }

                $units_string = '';
                foreach ($invoice->user->building_units as $unit) {
                    $units_string .= 'واحد ' . $unit->unit_number . " - ساختمان:" . $unit->building->name . "<br>";
                }

                Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                    new CustomMail(
                        'پرداخت خیریه - ' . $invoice->payment_tracenumber ?? "",
                        "نام کاربر : " . $invoice->user->full_name . "<br>" .
                            "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                            "شماره ارجاع : " . ($invoice->payment_tracenumber ?? "") . "<br>" .
                            "مبلغ کمیسیون : " . number_format(($commission ? $commission->amount : 0) * 10) . " ریال" . "<br>" .
                            "واحدهای کاربر : " . $units_string . "<br>"
                    )
                );
            }
        });
    }
}