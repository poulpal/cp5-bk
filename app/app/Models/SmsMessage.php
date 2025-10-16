<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $guarded = [];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function getUnitsAttribute($value)
    {
        return json_decode($value);
    }

    public function setUnitsAttribute($value)
    {
        $this->attributes['units'] = json_encode($value);
    }
}
