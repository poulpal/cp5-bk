<?php

namespace App\Models\Accounting;

use App\Models\Building;
use App\Models\BuildingUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $guarded = [];

    public function buildings()
    {
        return $this->belongsTo(Building::class);
    }

    public function parent()
    {
        return $this->belongsTo(AccountingAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccountingAccount::class, 'parent_id');
    }

    public function transactions()
    {
        return $this->hasMany(AccountingTransaction::class, 'accounting_account_id');
    }

    public function unit()
    {
        if ($this->accountable_type !== BuildingUnit::class) {
            return $this->belongsTo(BuildingUnit::class, 'accountable_id')->whereNull('id');
        }
        return $this->belongsTo(BuildingUnit::class, 'accountable_id')->withTrashed();
    }
}
