<?php

namespace App\Models;

use App\Models\Accounting\AccountingAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtType extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function receivableAccountingAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'receivable_accounting_account_id');
    }

    public function incomeAccountingAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'income_accounting_account_id');
    }
}
