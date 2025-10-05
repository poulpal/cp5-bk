<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingOptions extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'accounting_options' => '{
            "bank_code": "1601",
            "cash_code": "1603",
            "advance_received_code": "5701",
        }'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function setaccountingOptionsAttribute($value)
    {
        $this->attributes['accounting_options'] = json_encode($value);
    }

    public function getaccountingOptionsAttribute($value)
    {
        return json_decode($value);
    }
}
