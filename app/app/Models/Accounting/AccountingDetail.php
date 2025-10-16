<?php

namespace App\Models\Accounting;

use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingDetail extends Model
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
        return $this->hasMany(AccountingTransaction::class, 'accounting_detail_id');
    }

    public function accountable()
    {
        return $this->morphTo('accountable');
    }

    public function units()
    {
        return $this->accountable && $this->accountable_type == User::class ? $this->accountable->building_units()->where('building_id', $this->building_id) : null;
    }
}
