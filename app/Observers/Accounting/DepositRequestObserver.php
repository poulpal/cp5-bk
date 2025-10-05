<?php

namespace App\Observers\Accounting;

use App\Models\Accounting\AccountingDocument;
use App\Models\DepositRequest;
use Illuminate\Support\Facades\Log;

class DepositRequestObserver
{
    /**
     * Handle the DepositRequest "created" event.
     *
     * @param  \App\Models\DepositRequest  $depositRequest
     * @return void
     */
    public function created(DepositRequest $depositRequest)
    {
        if ($depositRequest->building?->name_en == 'atishahr') {
            try {
                $depositRequest = DepositRequest::find($depositRequest->id);
                if ($depositRequest->status !== 'accepted') return;
                $bank_code = $depositRequest->building->options->accounting_options->bank_code ?? '1601';
                $cash_code = $depositRequest->building->options->accounting_options->cash_code ?? '1603';

                $bank_detail = $depositRequest->building->accountingDetails()->where('type', 'bank')
                    ->where('name', 'LIKE', '%' . $depositRequest->sheba . '%')->first();

                $bank_detail_id = $bank_detail ? $bank_detail->id : $depositRequest->building->accountingDetails()->where('type', 'bank')->first()->id;

                $this->createDocument(
                    $depositRequest = $depositRequest,
                    $amount = $depositRequest->amount * 10,
                    $debit_id = $depositRequest->building->accountingAccounts()->where('code', $bank_code)->first()->id,
                    $debit_detail_id = $bank_detail_id,
                    $credit_id = $depositRequest->building->accountingAccounts()->where('code', $cash_code)->first()->id,
                    $credit_detail_id = $depositRequest->building->accountingDetails()->where('type', 'cash')->first()->id,
                    $date = $depositRequest->created_at,
                    $description = __('واریز از صندوق شارژپل به حساب IR') . $depositRequest->sheba
                );
            } catch (\Throwable $th) {
                Log::error($th);
            }
            return;
        }
        try {
            $depositRequest = DepositRequest::find($depositRequest->id);
            if ($depositRequest->status !== 'accepted') return;
            $bank_code = $depositRequest->building->options->accounting_options->bank_code ?? '1601';
            $cash_code = $depositRequest->building->options->accounting_options->cash_code ?? '1603';

            $this->createDocument(
                $depositRequest = $depositRequest,
                $amount = $depositRequest->amount * 10,
                $debit_id = $depositRequest->building->accountingAccounts()->where('code', $bank_code)->first()->id,
                $debit_detail_id = $depositRequest->building->accountingDetails()->where('type', 'bank')->first()->id,
                $credit_id = $depositRequest->building->accountingAccounts()->where('code', $cash_code)->first()->id,
                $credit_detail_id = $depositRequest->building->accountingDetails()->where('type', 'cash')->first()->id,
                $date = $depositRequest->created_at,
                $description = __('واریز از صندوق شارژپل به حساب IR') . $depositRequest->sheba
            );
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    /**
     * Handle the DepositRequest "updated" event.
     *
     * @param  \App\Models\DepositRequest  $depositRequest
     * @return void
     */
    public function updated(DepositRequest $depositRequest)
    {
        //
    }

    /**
     * Handle the DepositRequest "deleted" event.
     *
     * @param  \App\Models\DepositRequest  $depositRequest
     * @return void
     */
    public function deleted(DepositRequest $depositRequest)
    {
        //
    }

    /**
     * Handle the DepositRequest "restored" event.
     *
     * @param  \App\Models\DepositRequest  $depositRequest
     * @return void
     */
    public function restored(DepositRequest $depositRequest)
    {
        //
    }

    /**
     * Handle the DepositRequest "force deleted" event.
     *
     * @param  \App\Models\DepositRequest  $depositRequest
     * @return void
     */
    public function forceDeleted(DepositRequest $depositRequest)
    {
        //
    }

    private function createDocument($depositRequest, $amount, $debit_id, $debit_detail_id,  $credit_id, $credit_detail_id, $date = null, $description = null)
    {
        $new_document_number = AccountingDocument::where('building_id', $depositRequest->building_id)->max('document_number') + 1;
        $document = $depositRequest->accountingDocuments()->create([
            'building_id' => $depositRequest->building_id,
            'description' => $description ?? $depositRequest->description,
            'document_number' => $new_document_number,
            'amount' => $amount,
            'created_at' => $date ?? $depositRequest->created_at,
        ]);
        $document->transactions()->createMany([
            [
                'accounting_account_id' => $debit_id,
                'accounting_detail_id' => $debit_detail_id,
                'description' => $description ?? $depositRequest->description,
                'debit' => $amount,
                'credit' => 0,
                'created_at' => $document->created_at,
            ],
            [
                'accounting_account_id' => $credit_id,
                'accounting_detail_id' => $credit_detail_id,
                'description' => $description ?? $depositRequest->description,
                'debit' => 0,
                'credit' => $amount,
                'created_at' => $document->created_at,
            ],
        ]);
        return $document;
    }
}
