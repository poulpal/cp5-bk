<?php

namespace App\Models\Accounting;

use App\Models\Building;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $guarded = [];

    public function buildings()
    {
        return $this->belongsTo(Building::class);
    }

    public function transactions()
    {
        return $this->hasMany(AccountingTransaction::class);
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function invoice()
    {
        if ($this->documentable_type !== Invoice::class) {
            return $this->belongsTo(Invoice::class, 'documentable_id')->whereNull('id');
        }
        return $this->belongsTo(Invoice::class, 'documentable_id');
    }
}
