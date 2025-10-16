<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashController extends Controller
{

    public function index(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'unit' => 'required|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $unit = request()->unit ? auth()->user()->building_units()->find(request()->unit) : null;
        $building = $unit->building;

        if ($building->options->show_balances_to_units == false) {
            return abort(200);
        }


        $cashs = $building->accountingDetails()->where(function ($query) {
            $query->where('type', 'cash');
            // ->orWhere('type', 'bank');
        })->get();

        foreach ($cashs as $cash) {
            if ($cash->name == __("صندوق شارژپل")) {
                $cash->balance = $building->balance + $building->toll_balance;
            } else {
                $cash->balance =
                    ($building->accountingTransactions()->where('accounting_detail_id', $cash->id)->sum('debit') -
                    $building->accountingTransactions()->where('accounting_detail_id', $cash->id)->sum('credit')) / 10;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cashs' => $cashs,
            ]
        ]);
    }
}
