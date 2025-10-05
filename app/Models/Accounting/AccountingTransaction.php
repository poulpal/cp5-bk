<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $guarded = [];

    public function document()
    {
        return $this->belongsTo(AccountingDocument::class, 'accounting_document_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    public function detail()
    {
        return $this->belongsTo(AccountingDetail::class, 'accounting_detail_id');
    }
}
