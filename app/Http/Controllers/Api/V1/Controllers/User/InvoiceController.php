<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::where('status', 'paid')
            ->whereNot('serviceable_type', 'App\Models\Commission')
            // ->whereNot('payment_method', 'cash')
            ->where(function ($query) {
                $query->where('user_id', auth()->user()->id)
                ->orWhere( function ($query) {
                    $query->where('serviceable_type', 'App\Models\BuildingUnit')
                    ->whereIn('serviceable_id', auth()->user()->building_units->pluck('id'));
                });
            });
        $invoices = $invoices->orderBy('id', 'desc');
        $invoices = $invoices->paginate(20);
        return view('user.invoices.index')->with([
            'invoices' => $invoices,
        ]);
    }


    // public function show(Invoice $invoice)
    // {
    //     if ($invoice->business_id != auth()->buildingManager()->business->id) {
    //         return redirect()->back()->withErrors(['invoice' => 'فاکتور مورد نظر یافت نشد.']);
    //     }
    //     return view('buildingManager.invoices.show')->with([
    //         'invoice' => $invoice,
    //     ]);
    // }
}
