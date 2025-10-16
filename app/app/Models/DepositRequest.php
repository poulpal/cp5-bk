<?php

namespace App\Models;

use App\Models\Accounting\AccountingDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }


    public function accountingDocuments()
    {
        return $this->morphMany(AccountingDocument::class , 'documentable');
    }

    public function balance()
    {
        return $this->belongsTo(Balance::class);
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    public function getDataAttribute($value)
    {
        return json_decode($value);
    }
}
