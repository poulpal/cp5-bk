<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservable extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'available_hours' => 'array'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class)->where('status', 'paid');
    }

    public function active_reservations()
    {
        return $this->hasMany(Reservation::class)->where('end_time', '>', now())->where('status', 'paid');
    }

    public function getAvailableHoursAttribute($value)
    {
        return json_decode($value);
    }

    public function setAvailableHoursAttribute($value)
    {
        $this->attributes['available_hours'] = json_encode($value);
    }
}
