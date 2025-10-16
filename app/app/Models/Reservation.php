<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function reservable()
    {
        return $this->belongsTo(Reservable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
