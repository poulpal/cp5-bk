<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = json_encode($value);
    }

    public function getOptionsAttribute($value)
    {
        return json_decode($value);
    }

    public function votes()
    {
        return $this->hasMany(PollVotes::class);
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
