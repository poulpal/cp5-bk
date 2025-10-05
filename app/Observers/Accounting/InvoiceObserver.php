<?php

namespace App\Observers\Accounting;

use App\Models\Accounting\AccountingDocument;
use App\Models\BuildingUnit;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function created(Invoice $invoice)
    {
        $this->calculateDebts($invoice);
        $this->handleCreate($invoice);
        try {
            $invoice = Invoice::find($invoice->id);
            if (!$invoice->is_verified) return;
            if (!$invoice->building_id) return;
            if ($invoice->status !== 'paid') return;

            $hasUnit = $invoice->serviceable_type == BuildingUnit::class;

            if ($hasUnit) {
                if ($invoice->amount < 0) {
                    $this->createDebtDocument($invoice);
                }
                if ($invoice->amount > 0) {
                    $this->createDepositDocument($invoice);
                }
            } else {
                if ($invoice->amount < 0) {
                    $this->createCostDocument($invoice);
                }
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function updated(Invoice $invoice)
    {
        $this->calculateDebts($invoice);
        try {
            // if (!$invoice->is_verified) return;

            if ($invoice->unit) {
                // on online payment status change
                if ($invoice->isDirty('status') && $invoice->status == 'paid') {
                    $this->handleCreate($invoice);
                    $invoice = Invoice::find($invoice->id);
                    $this->createDepositDocument($invoice);
                }
                // when transaction amount is changed
                // if ($invoice->isDirty('amount')) {
                //     $last_document = $invoice->accountingDocuments()->latest('id')->first();
                //     if ($last_document) {
                //         $this->reverseDocument($last_document);
                //     }
                //     $this->handleDelete($invoice);
                //     $this->handleCreate($invoice);
                //     $invoice = Invoice::find($invoice->id);
                //     if ($invoice->amount < 0) {
                //         $this->createDebtDocument($invoice);
                //     }
                //     if ($invoice->amount > 0) {
                //         $this->createDepositDocument($invoice);
                //     }
                // }

                // when manual payment is verified
                if ($invoice->isDirty('is_verified') && $invoice->is_verified) {
                    $this->created($invoice);
                }
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function deleted(Invoice $invoice)
    {
        $this->calculateDebts($invoice);
        $this->handleDelete($invoice);
        try {
            if (!$invoice->is_verified) return;

            if ($invoice->unit) {
                $last_document = $invoice->accountingDocuments()->latest('id')->first();
                if (!$last_document) return;
                if ($invoice->amount < 0) {
                    $this->reverseDocument($last_document);
                }
                if ($invoice->amount > 0) {
                    $this->reverseDocument($last_document);
                }
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    /**
     * Handle the Invoice "restored" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function restored(Invoice $invoice)
    {
        $this->calculateDebts($invoice);
    }

    /**
     * Handle the Invoice "force deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function forceDeleted(Invoice $invoice)
    {
        $this->calculateDebts($invoice);
    }

    private function createDocument($invoice, $amount, $debit_id, $debit_detail_id = null, $credit_id, $credit_detail_id = null, $date = null, $description = null)
    {
        $new_document_number = AccountingDocument::where('building_id', $invoice->building_id)->max('document_number') + 1;
        $document = $invoice->accountingDocuments()->create([
            'building_id' => $invoice->building_id,
            'description' => $description ?? $invoice->description,
            'document_number' => $new_document_number,
            'amount' => $amount,
            'created_at' => $date ?? $invoice->created_at,
        ]);
        $document->transactions()->createMany([
            [
                'accounting_account_id' => $debit_id,
                'accounting_detail_id' => $debit_detail_id ?? null,
                'description' => $description ?? $invoice->description,
                'debit' => $amount,
                'credit' => 0,
                'created_at' => $document->created_at,
            ],
            [
                'accounting_account_id' => $credit_id,
                'accounting_detail_id' => $credit_detail_id ?? null,
                'description' => $description ?? $invoice->description,
                'debit' => 0,
                'credit' => $amount,
                'created_at' => $document->created_at,
            ],
        ]);
        return $document;
    }

    private function createDebtDocument($invoice)
    {
        $new_document_number = AccountingDocument::where('building_id', $invoice->building_id)->max('document_number') + 1;
        $document = $invoice->accountingDocuments()->create([
            'building_id' => $invoice->building_id,
            'description' => $invoice->description . __(' - واحد ') . $invoice->unit->unit_number,
            'document_number' => $new_document_number,
            'amount' => -1 * $invoice->amount * 10,
            'created_at' => $invoice->created_at,
        ]);
        $detail_id = $invoice->building->accountingDetails()->where('accountable_id', $invoice->unit->id)
            ->where('accountable_type', BuildingUnit::class)->first()->id;

        $document->transactions()->create([
            'accounting_account_id' => $invoice->debtType->receivable_accounting_account_id,
            'accounting_detail_id' => $detail_id,
            'description' => $document->description,
            'debit' => -1 * $invoice->amount * 10,
            'credit' => 0,
            'created_at' => $document->created_at,
        ]);

        $document->transactions()->create([
            'accounting_account_id' => $invoice->debtType->income_accounting_account_id,
            'accounting_detail_id' => null,
            'description' => $document->description,
            'debit' => 0,
            'credit' => -1 * $invoice->amount * 10,
            'created_at' => $document->created_at,
        ]);

        $advance_received_code = $invoice->building->options->accounting_options->advance_received_code ?? '5701';
        $deposits = Invoice::whereJsonContains('paid_data', [['debt_id' => $invoice->id]])->get();
        foreach ($deposits as $deposit) {
            $document->transactions()->create([
                'accounting_account_id' => $invoice->building->accountingAccounts()->where('code', $advance_received_code)->first()->id,
                'accounting_detail_id' => $detail_id,
                'description' => __("اصلاح پیش دریافت از واحد ") . $invoice->unit->unit_number . " - " . $invoice->description,
                'debit' => $deposit->paid_data[array_search($invoice->id, array_column($deposit->paid_data, 'debt_id'))]->amount * 10,
                'credit' => 0,
                'created_at' => $document->created_at,
            ]);
            $document->transactions()->create([
                'accounting_account_id' => $invoice->debtType->receivable_accounting_account_id,
                'accounting_detail_id' => $detail_id,
                'description' => __("اصلاح پیش دریافت از واحد ") . $invoice->unit->unit_number . " - " . $invoice->description,
                'debit' => 0,
                'credit' => $deposit->paid_data[array_search($invoice->id, array_column($deposit->paid_data, 'debt_id'))]->amount * 10,
                'created_at' => $document->created_at,
            ]);
        }

        $document->update([
            'amount' => $document->transactions()->sum('debit'),
        ]);
    }

    private function createDepositDocument($invoice)
    {
        $new_document_number = AccountingDocument::where('building_id', $invoice->building_id)->max('document_number') + 1;
        $document = $invoice->accountingDocuments()->create([
            'building_id' => $invoice->building_id,
            'description' => $invoice->description . __(' - واحد ') . $invoice->unit->unit_number,
            'document_number' => $new_document_number,
            'amount' => $invoice->amount * 10,
            'created_at' => $invoice->created_at,
        ]);
        $is_online = $invoice->payment_method !== 'cash' || ($invoice->bank->type == 'cash');
        $bank_code = $invoice->building->options->accounting_options->bank_code ?? '1601';
        $cash_code = $invoice->building->options->accounting_options->cash_code ?? '1603';

        $document->transactions()->create([
            'accounting_account_id' => $is_online ?
                $invoice->building->accountingAccounts()->where('code', $cash_code)->first()->id :
                $invoice->building->accountingAccounts()->where('code', $bank_code)->first()->id,
            'accounting_detail_id' => $is_online ?
                $invoice->bank_id ?? $invoice->building->accountingDetails()->where('type', 'cash')->first()->id :
                $invoice->bank_id ?? $invoice->building->accountingDetails()->where('type', 'bank')->first()->id,
            'description' => $document->description,
            'debit' => $invoice->amount * 10,
            'credit' => 0,
            'created_at' => $document->created_at,
        ]);

        $detail_id = $invoice->building->accountingDetails()->where('accountable_id', $invoice->unit->id)
            ->where('accountable_type', BuildingUnit::class)->first()->id;

        foreach ($invoice->paid_data ?? [] as $paid_data) {
            $debt = Invoice::find($paid_data->debt_id);
            $document->transactions()->create([
                'accounting_account_id' => $debt->debtType->receivable_accounting_account_id,
                'accounting_detail_id' => $detail_id,
                'description' => $debt->description,
                'debit' => 0,
                'credit' => $paid_data->amount * 10,
                'created_at' => $document->created_at,
            ]);
        }

        $advance_received_code = $invoice->building->options->accounting_options->advance_received_code ?? '5701';

        if ($invoice->amount > $invoice->paid_amount) {
            $document->transactions()->create([
                'accounting_account_id' => $invoice->building->accountingAccounts()->where('code', $advance_received_code)->first()->id,
                'accounting_detail_id' => $detail_id,
                'description' => __("پیش دریافت از واحد ") . $invoice->unit->unit_number . " - " . $invoice->description,
                'debit' => 0,
                'credit' => ($invoice->amount - $invoice->paid_amount) * 10,
                'created_at' => $document->created_at,
            ]);
        }
    }

    private function createCostDocument($invoice)
    {
        $new_document_number = AccountingDocument::where('building_id', $invoice->building_id)->max('document_number') + 1;
        $document = $invoice->accountingDocuments()->create([
            'building_id' => $invoice->building_id,
            'description' => $invoice->description,
            'document_number' => $new_document_number,
            'amount' => -1 * $invoice->amount * 10,
            'created_at' => $invoice->created_at,
        ]);
        $is_cash = $invoice->bank && $invoice->bank->type == 'cash';
        $bank_code = $invoice->building->options->accounting_options->bank_code ?? '1601';
        $cash_code = $invoice->building->options->accounting_options->cash_code ?? '1603';

        Log::info('Creating cost document for invoice: ' . $invoice->id);
        Log::info('Bank code: ' . $bank_code);
        Log::info('Cash code: ' . $cash_code);
        Log::info('Is cash: ' . ($is_cash ? 'Yes' : 'No'));

        $cost_code = '7';

        $document->transactions()->create([
            'accounting_account_id' => $invoice->from_account_id ?? $invoice->building->accountingAccounts()->where('code', $cost_code)->first()->id,
            'accounting_detail_id' => null,
            'description' => $invoice->description,
            'debit' => -1 * $invoice->amount * 10,
            'credit' => 0,
            'created_at' => $document->created_at,
        ]);

        $document->transactions()->create([
            'accounting_account_id' => $is_cash ?
                $invoice->building->accountingAccounts()->where('code', $cash_code)->first()->id :
                $invoice->building->accountingAccounts()->where('code', $bank_code)->first()->id,
            'accounting_detail_id' => $is_cash ?
                $invoice->bank_id ?? $invoice->building->accountingDetails()->where('type', 'cash')->first()->id :
                $invoice->bank_id ?? $invoice->building->accountingDetails()->where('type', 'bank')->first()->id,
            'description' => $invoice->description,
            'debit' => 0,
            'credit' => -1 * $invoice->amount * 10,
            'created_at' => $document->created_at,
        ]);

        $document->update([
            'amount' => $document->transactions()->sum('debit'),
        ]);
    }

    private function handleCreate(Invoice $invoice)
    {
        try {
            $invoice = Invoice::find($invoice->id);
            if (!$invoice->is_verified) return;
            if (!$invoice->building_id) return;
            if ($invoice->status !== 'paid') return;
            if (!$invoice->unit) return;

            $unit = $invoice->unit;
            if ($invoice->amount > 0) {
                $pending_debts = $unit->invoices()
                    ->where('amount', '<', 0)
                    ->where('status', 'paid')
                    ->where('is_verified', 1)
                    ->where('created_at', '<=', $invoice->created_at)
                    ->where('is_paid', 0)
                    ->where('resident_type', $invoice->resident_type)
                    ->orderBy('created_at', 'asc')
                    ->get();
                foreach ($pending_debts as $pending_debt) {
                    $deposit_amount = $invoice->amount - $invoice->paid_amount;
                    $debt_amount = (-1 * $pending_debt->amount) - $pending_debt->paid_amount;
                    if ($deposit_amount >= $debt_amount) {
                        $invoice->paid_amount += $debt_amount;
                        $pending_debt->paid_amount += $debt_amount;
                        $pending_debt->is_paid = 1;
                        $pending_debt->savequietly();
                        $invoice->paid_data = array_merge($invoice->paid_data ?? [], [[
                            'debt_id' => $pending_debt->id,
                            'amount' => round($debt_amount, 1),
                        ]]);
                        $invoice->savequietly();
                    } else {
                        $invoice->paid_amount += $deposit_amount;
                        $pending_debt->paid_amount += $deposit_amount;
                        $pending_debt->savequietly();
                        $invoice->paid_data = array_merge($invoice->paid_data ?? [], [[
                            'debt_id' => $pending_debt->id,
                            'amount' => round($deposit_amount, 1),
                        ]]);
                        $invoice->savequietly();
                        break;
                    }
                }
                if ($invoice->amount == $invoice->paid_amount) {
                    $invoice->is_paid = 1;
                    $invoice->savequietly();
                }
            }
            if ($invoice->amount < 0) {
                $pending_deposits = $unit->invoices()
                    ->where('status', 'paid')
                    ->where('amount', '>', 0)
                    ->where('is_verified', 1)
                    ->where('created_at', '<=', $invoice->created_at)
                    ->where('is_paid', 0)
                    ->where('resident_type', $invoice->resident_type)
                    ->orderBy('created_at', 'asc')
                    ->get();
                $debt_amount = (-1 * $invoice->amount) - $invoice->paid_amount;
                foreach ($pending_deposits as $pending_deposit) {
                    $deposit_amount = ($pending_deposit->amount - $pending_deposit->paid_amount);
                    if ($debt_amount >= $deposit_amount) {
                        $pending_deposit->is_paid = 1;
                        $pending_deposit->paid_amount += $deposit_amount;
                        $pending_deposit->paid_data = array_merge($pending_deposit->paid_data ?? [], [[
                            'debt_id' => $invoice->id,
                            'amount' => round($deposit_amount, 1),
                        ]]);
                        $pending_deposit->savequietly();
                        $invoice->paid_amount += $deposit_amount;
                        $invoice->savequietly();
                    } else {
                        $pending_deposit->paid_amount += $debt_amount;
                        $pending_deposit->paid_data = array_merge($pending_deposit->paid_data ?? [], [[
                            'debt_id' => $invoice->id,
                            'amount' => round($debt_amount, 1),
                        ]]);
                        $pending_deposit->savequietly();
                        $invoice->paid_amount += $debt_amount;
                        $invoice->is_paid = 1;
                        $invoice->savequietly();
                        break;
                    }
                }
                if ($invoice->amount == -1 * $invoice->paid_amount) {
                    $invoice->is_paid = 1;
                    $invoice->savequietly();
                }
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    private function handleDelete(Invoice $invoice)
    {
        try {
            if (!$invoice->is_verified) return;
            if (!$invoice->building_id) return;
            if ($invoice->status !== 'paid') return;

            if ($invoice->amount > 0) {
                foreach ($invoice->paid_data ?? [] as $paid_data) {
                    $debt = Invoice::find($paid_data->debt_id);
                    $debt->paid_amount -= $paid_data->amount;
                    $debt->is_paid = 0;
                    $debt->savequietly();
                }
            }
            if ($invoice->amount < 0) {
                $deposits = Invoice::whereJsonContains('paid_data', [['debt_id' => $invoice->id]])->get();
                foreach ($deposits as $deposit) {
                    $deposit->is_paid = 0;
                    $deposit->paid_amount -= $deposit->paid_data[array_search($invoice->id, array_column($deposit->paid_data, 'debt_id'))]->amount;
                    $deposit->paid_data = array_values(array_filter($deposit->paid_data, function ($item) use ($invoice) {
                        return $item->debt_id != $invoice->id;
                    }));
                    $deposit->savequietly();
                }
            }
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    private function reverseDocument($document)
    {
        $new_document_number = AccountingDocument::where('building_id', $document->building_id)->max('document_number') + 1;
        $reverse_document = $document->replicate();
        $reverse_document->document_number = $new_document_number;
        $reverse_document->description = __("اصلاحیه سند ") . $document->document_number;
        $reverse_document->amount = $document->amount;
        $reverse_document->save();
        foreach ($document->transactions as $transaction) {
            $reverse_document->transactions()->create([
                'accounting_account_id' => $transaction->accounting_account_id,
                'accounting_detail_id' => $transaction->accounting_detail_id,
                'description' => $transaction->description,
                'debit' => $transaction->credit,
                'credit' => $transaction->debit,
                'created_at' => $reverse_document->created_at,
            ]);
        }
    }

    private function calculateDebts(Invoice $invoice)
    {
        if ($invoice->serviceable_type == BuildingUnit::class) {
            $buildingUnit = $invoice->unit;
            $buildingUnit->resident_debt = $buildingUnit->debt('resident');
            $buildingUnit->owner_debt = $buildingUnit->debt('owner');
            $buildingUnit->charge_debt = $buildingUnit->debt();
            $buildingUnit->saveQuietly();
        }
    }
}
