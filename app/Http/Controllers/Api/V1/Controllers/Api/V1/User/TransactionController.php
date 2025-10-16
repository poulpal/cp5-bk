<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $invoices = Invoice::where('status', 'paid')
            ->where('is_verified', true)
            ->where('user_id', auth()->user()->id)
            ->where('amount', '>', 0)
            ->whereNull('deleted_at')
            ->whereNot('serviceable_type', 'App\Models\Commission');

        $invoices = $invoices->orderBy('id', 'desc');
        $invoices = $invoices->withTrashed('serviceable');
        $invoices = $invoices->get();
        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => InvoiceResource::collection($invoices),
            ]
        ]);
    }
}
