<?php

namespace App\Http\Controllers\BuildingManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepositRequestController extends Controller
{
    public function index()
    {
        $depositRequests = auth()->buildingManager()->business->depositRequests()->orderBy('created_at', 'desc')->paginate(20);
        return view('buildingManager.depositRequests.index')->with([
            'depositRequests' => $depositRequests,
        ]);
    }

    public function create()
    {
        $business = auth()->buildingManager()->business;
        $balance = $business->balance;
        return view('buildingManager.depositRequests.create')->with([
            'balance' => $balance,
            'sheba' => $business->sheba_number,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:50000',
            'deposit_to' => 'required|in:me,other',
            'sheba' => 'required_if:deposit_to,other|nullable',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $business = auth()->buildingManager()->business;

        if ($request->amount > $business->balance) {
            return redirect()->back()->withErrors(['مبلغ درخواستی از موجودی حساب شما بیشتر است.'])->withInput();
        }

        if ($request->deposit_to == 'me' && $request->sheba != $business->sheba_number) {
            return redirect()->back()->withErrors(['شماره شبا وارد شده صحیح نیست.'])->withInput();
        }

        $pending_requests = $business->depositRequests()->where('status', 'pending')->get();
        if ($pending_requests->count() > 0) {
            return redirect()->back()->withErrors(['شما درخواست واریز دیگری در انتظار پاسخ دارید.'])->withInput();
        }

        $business->depositRequests()->create([
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => 'pending',
            'sheba' => $request->sheba,
            'deposit_to' => $request->deposit_to,
        ]);

        return redirect()->route('building_manager.deposit_requests.index')->with([
            'success' => 'درخواست واریز با موفقیت ثبت شد.',
        ]);
    }
}
