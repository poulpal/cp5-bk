<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Accounting\AccountingAccount;
use App\Observers\Accounting\BuildingUnitObserver;
use Carbon\Carbon;

class BuildingUnit extends Pivot
{
    use SoftDeletes;

    protected $table = 'building_units';
    protected $guarded = [];
    public $incrementing = true;

    // add uniqid on store
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $uniqid = random_int(100000, 999999);
                $exists = self::where('token', $uniqid)->exists();
            } while ($exists);
            $model->token = $uniqid;
        });

        static::updating(function ($model) {
            $model->charge_debt = round($model->charge_debt, 1);
        });
    }

    public function residents()
    {
        return $this->belongsToMany(User::class, 'building_units_users', 'building_unit_id', 'user_id')
            ->whereNull('building_units_users.deleted_at')
            ->withPivot(['id', 'ownership', 'created_at', 'updated_at', 'deleted_at']);
    }

    public function residentsWithTrashed()
    {
        return $this->belongsToMany(User::class, 'building_units_users', 'building_unit_id', 'user_id')
            ->withPivot(['id', 'ownership', 'created_at', 'updated_at', 'deleted_at']);
    }

    public function residentsByDate($date)
    {
        return $this->residentsWithTrashed()->wherePivot('created_at', '<=', $date)->where(function ($query) use ($date) {
            $query->where('building_units_users.deleted_at', '>=', $date)
            ->orWhere('building_units_users.deleted_at', null);
        });
    }

    public function getOwnerAttribute()
    {
        return $this->residents()->where('ownership', 'owner')->first();
    }

    public function getRenterAttribute()
    {
        return $this->residents()->where('ownership', 'renter')->first();
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'serviceable');
    }

    public function tolls()
    {
        return $this->morphMany(Toll::class, 'serviceable');
    }

    public function  balance()
    {
        return $this->belongsTo(Balance::class);
    }

    // public function accountingAccount()
    // {
    //     return $this->morphOne(AccountingAccount::class, 'accountable');
    // }

    // public function getAccountingAccountAttribute()
    // {
    //     if ($this->accountingAccount()->exists()) {
    //         return $this->accountingAccount()->first();
    //     }
    //     $building = $this->building;
    //     $parent_account = $building->accountingAccounts()->where('code', 'like', '1305')->first();
    //     $max_code = $parent_account->children()->max('code') ?? 13050000;
    //     $code = $max_code + 1;
    //     $account = $this->accountingAccount()->create([
    //         'building_id' => $building->id,
    //         'name' => 'واحد ' . $this->unit_number,
    //         'code' => $code,
    //         'parent_id' => $parent_account->id,
    //         'type' => 'debit',
    //     ]);
    //     return $account;
    // }

    public function getChargeDebtAttribute($value)
    {
        return round($value, 1);
    }

    public function setChargeDebtAttribute($value)
    {
        $this->attributes['charge_debt'] = round($value, 1);
    }

    public function debt($resident_type = null)
    {
        $invoices = $this->invoices()
            ->where('status', 'paid')
            ->where('is_verified', 1);
        if ($resident_type) {
            $invoices = $invoices->where('resident_type', $resident_type);
        }
        return -1 * ($invoices->sum('amount'));

        // $debts = $this->invoices()
        //     ->where('amount' , '<', 0)
        //     ->where('status', 'paid')
        //     ->where('is_verified', 1)
        //     ->where('is_paid', 0);
        // if ($resident_type) {
        //     $debts = $debts->where('resident_type', $resident_type);
        // }
        // $debt_sum = -1 * ($debts->sum(\DB::raw('amount + paid_amount')));


        // $deposits = $this->invoices()
        //     ->where('amount' , '>', 0)
        //     ->where('status', 'paid')
        //     ->where('is_verified', 1)
        //     ->where('is_paid', 0);
        // if ($resident_type) {
        //     $deposits = $deposits->where('resident_type', $resident_type);
        // }
        // $deposit_sum = $deposits->sum(\DB::raw('amount - paid_amount'));

        $invoices = $this->invoices()
            ->where('status', 'paid')
            ->where('is_verified', 1)
            ->where('is_paid', 0);
        if ($resident_type) {
            $invoices = $invoices->where('resident_type', $resident_type);
        }
        $total_debt = -1 * $invoices->sum(\DB::raw('amount - SIGN(amount) * paid_amount'));
        return $total_debt;
    }

    public function userDebt($user)
    {
        $ownership = $this->residents()->where('user_id', $user->id)->first()->pivot->ownership;

        $resident_type = $ownership;
        if ($ownership == 'owner' && $this->residents()->count() == 1) {
            $resident_type = 'resident';
        }
        if ($ownership == 'renter') {
            $resident_type = 'resident';
        }

        return -1 * $this->invoices()->where(function ($query) use ($resident_type, $ownership) {
            $query->where('resident_type', 'all')
                ->orWhere('resident_type', $ownership)
                ->orWhere('resident_type', $resident_type);
        })->where('status', 'paid')->where('is_verified', 1)->sum('amount');
    }

    public function userDiscount($user)
    {
        $ownership = $this->residents()->where('user_id', $user->id)->first()->pivot->ownership;

        $resident_type = $ownership;
        if ($ownership == 'owner' && $this->residents()->count() == 1) {
            $resident_type = 'resident';
        }
        if ($ownership == 'renter') {
            $resident_type = 'resident';
        }

        return $this->invoices()->where(function ($query) use ($resident_type, $ownership) {
            $query->where('resident_type', 'all')
                ->orWhere('resident_type', $ownership)
                ->orWhere('resident_type', $resident_type);
            })->where('early_discount_until', '>=', Carbon::now())->sum('early_discount_amount');
    }
}
