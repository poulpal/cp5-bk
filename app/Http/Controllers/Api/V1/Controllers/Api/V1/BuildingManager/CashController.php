<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingDocument;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index']);
    }

    public function index(Request $request)
    {
        $cashs = auth()->buildingManager()->building->accountingDetails()->where(function ($query) {
            $query->where('type', 'cash');
                // ->orWhere('type', 'bank');
        })->get();

        foreach ($cashs as $cash) {
            if ($cash->name == "صندوق شارژپل") {
                $cash->balance = auth()->buildingManager()->building->balance + auth()->buildingManager()->building->toll_balance;
            } else {
                $cash->balance =
                    (auth()->buildingManager()->building->accountingTransactions()->where('accounting_detail_id', $cash->id)->sum('debit') -
                        auth()->buildingManager()->building->accountingTransactions()->where('accounting_detail_id', $cash->id)->sum('credit')) / 10;
            }
            $cash->name = __($cash->name);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cashs' => $cashs,
            ]
        ]);
    }

    public function changeBalance(Request $request, $cash_id)
    {
        $validator = validator($request->all(), [
            'balance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $building = auth()->buildingManager()->building;

        $cash = $building->accountingDetails()->where('type', 'cash')->find($cash_id);
        if (!$cash) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'cash_id' => ['صندوق مورد نظر یافت نشد.']
                ],
            ], 404);
        }

        $previousBalance = (auth()->buildingManager()->building->accountingTransactions()->where('accounting_detail_id', $cash_id)->sum('debit') -
            auth()->buildingManager()->building->accountingTransactions()->where('accounting_detail_id', $cash_id)->sum('credit'));
        $newBalance = $request->balance * 10;

        // dd($previousBalance, $newBalance);

        if ($newBalance > $previousBalance) {
            $amount = $newBalance - $previousBalance;
            $debit_id = $building->accountingAccounts()->where('code', 1603)->first()->id;;
            $credit_id = $building->accountingAccounts()->where('code', 30)->first()->id;
            $debit_detail_id = $cash->id;
            $credit_detail_id = null;
        } else {
            $amount = $previousBalance - $newBalance;
            $debit_id = $building->accountingAccounts()->where('code', 30)->first()->id;
            $credit_id = $building->accountingAccounts()->where('code', 1603)->first()->id;
            $debit_detail_id = null;
            $credit_detail_id = $cash->id;
        }

        $document = $this->createDocument($building, $amount, $debit_id, $debit_detail_id, $credit_id, $credit_detail_id, now(), __("تغییر موجودی صندوق"));

        return response()->json([
            'success' => true,
            'message' => __("موجودی صندوق با موفقیت تغییر یافت."),
        ]);
    }

    private function createDocument($building, $amount, $debit_id, $debit_detail_id = null, $credit_id, $credit_detail_id = null, $date = null, $description = null)
    {
        $new_document_number = AccountingDocument::where('building_id', $building->id)->max('document_number') + 1;
        $document = $building->accountingDocuments()->create([
            'building_id' => $building->id,
            'description' => $description,
            'document_number' => $new_document_number,
            'amount' => $amount,
            'created_at' => $date ?? $building->created_at,
        ]);
        $document->transactions()->createMany([
            [
                'accounting_account_id' => $debit_id,
                'accounting_detail_id' => $debit_detail_id ?? null,
                'description' => $description,
                'debit' => $amount,
                'credit' => 0,
                'created_at' => $document->created_at,
            ],
            [
                'accounting_account_id' => $credit_id,
                'accounting_detail_id' => $credit_detail_id ?? null,
                'description' => $description,
                'debit' => 0,
                'credit' => $amount,
                'created_at' => $document->created_at,
            ],
        ]);
        return $document;
    }
}
