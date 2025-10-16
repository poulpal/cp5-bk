<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function getFeaturesAttribute($value)
    {
        return json_decode($value);
    }

    public function getDurationsAttribute($value)
    {
        return json_decode($value);
    }

    public function setFeaturesAttribute($value)
    {
        $this->attributes['features'] = json_encode($value);
    }

    public function setDurationsAttribute($value)
    {
        $this->attributes['durations'] = json_encode($value);
    }
}
