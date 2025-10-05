<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Toll extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $uniqid = Str::random(10);
                $exists = self::where('token', $uniqid)->exists();
            } while ($exists);
            $model->token = $uniqid;
        });
    }

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
        return $this->BelongsTo(BuildingUnit::class, 'serviceable_id');
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'serviceable');
    }
}
