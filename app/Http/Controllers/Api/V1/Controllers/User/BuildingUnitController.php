<?php

namespace App\Http\Controllers\User;

use App\Facades\CommissionHelper;
use App\Http\Controllers\Controller;
use App\Models\BuildingUnit;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Morilog\Jalali\Jalalian;

class BuildingUnitController extends Controller
{
    public function index()
    {
        $units = auth()->user()->building_units()->with('building')->get();
        return view('user.units.index')->with([
            'units' => $units,
        ]);
    }

    public function pay(Request $request)
    {
        $unit = BuildingUnit::find($request->building_unit_id);
        if (!$unit || $unit->residents()->where('user_id', auth()->user()->id)->count() == 0) {
            return redirect()->back()->withErrors(['building_unit_user_id' => 'واحد مورد نظر یافت نشد.'])->withInput();
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'building_unit_id' => 'required|numeric|exists:building_units,id',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $commission_amount = CommissionHelper::calculateMaxCommission($unit->building);
        app('redirect')->setIntendedUrl(route('user.units.index'));
        $payment_invoice = (new Invoice)->amount($request->amount + $commission_amount)->detail('mobile', auth()->user()->mobile);
        $payment = Payment::purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($unit, $request, $commission_amount, $payment_invoice) {
                $invoice = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "Shetabit\Multipay\Drivers\Sepehr\Sepehr" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver),
                    'amount' => $request->amount,
                    'business_id' => $unit->building->buildingManager->business->id,
                    'serviceable_id' => $unit->id,
                    'serviceable_type' => BuildingUnit::class,
                    'description' => 'پرداخت شارژ - واحد ' . $unit->unit_number,
                ]);

                $commission = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "Shetabit\Multipay\Drivers\Sepehr\Sepehr" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver),
                    'amount' => $commission_amount,
                    'business_id' => $unit->building->buildingManager->business->id,
                    'serviceable_type' => Commission::class,
                    'description' => 'کمیسیون پرداخت شارژ - واحد ' . $unit->unit_number,
                ]);
            }
        )->pay()->render();
        return $payment;
        // $payment = json_decode($payment);
        // return redirect($payment->action);
    }

}
