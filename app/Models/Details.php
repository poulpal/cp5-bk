<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Details extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function owner()
    {
        return $this->belongsTo(BuildingManager::class, 'user_id');
    }

    public function building()
    {
        if (!$this->owner->role == 'building_manager') return null;
        return $this->belongsTo(Building::class);
    }
}
