<?php

namespace App\Http\Controllers\BuildingManager;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $validator = validator(request()->all(), [
            'mobile' => 'nullable|numeric|digits:11',
            'building_unit_id' => 'nullable|numeric|exists:building_units,id',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $invoices = auth()->buildingManager()->business->invoices()->where('status', 'paid')->whereNot('serviceable_type', Commission::class)->orderBy('id', 'desc');

        if (request()->has('building_unit_id')) {
            $invoices = $invoices->where('serviceable_id', request()->building_unit_id)->where('serviceable_type', 'App\Models\BuildingUnit');
        }
        $invoices = $invoices->get();

        $balance = 0;

        foreach ($invoices->reverse() as $invoice) {
            $balance += $invoice->amount;
            $invoice->balance = $balance;
        }

        $invoices = $invoices->paginate(20);
        return view('buildingManager.invoices.index')->with([
            'invoices' => $invoices,
            'business' => auth()->buildingManager()->business,
        ]);
    }


    public function show(Invoice $invoice)
    {
        if ($invoice->business_id != auth()->buildingManager()->business->id) {
            return redirect()->back()->withErrors(['invoice' => 'فاکتور مورد نظر یافت نشد.']);
        }
        return view('buildingManager.invoices.show')->with([
            'invoice' => $invoice,
        ]);
    }
}
