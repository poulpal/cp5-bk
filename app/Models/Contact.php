<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'name',
        'mobile',
        'category',
        'admins_only',
    ];

    protected $casts = [
        'admins_only' => 'boolean',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

}
