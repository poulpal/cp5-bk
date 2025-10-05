<?php

namespace App\Models;

use App\Models\Accounting\AccountingDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function service()
    {
        return $this->morphTo('serviceable');
    }

    public function unit()
    {
        return $this->BelongsTo(BuildingUnit::class, 'serviceable_id')->withTrashed();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function accountingDocuments()
    {
        return $this->morphMany(AccountingDocument::class, 'documentable');
    }

    public function discount_code()
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function debtType()
    {
        return $this->belongsTo(DebtType::class);
    }

    public function bank()
    {
        return $this->belongsTo(AccountingDetail::class, 'bank_id')
            ->where('type', 'bank')->orWhere('type', 'cash');
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    public function getDataAttribute($value)
    {
        return json_decode($value);
    }

    public function setPaidDataAttribute($value)
    {
        $this->attributes['paid_data'] = json_encode($value);
    }

    public function getPaidDataAttribute($value)
    {
        return json_decode($value);
    }
}
