<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Factor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $uniqid = Str::random(12);
                $exists = self::where('token', $uniqid)->exists();
            } while ($exists);
            $model->token = $uniqid;
        });
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function getItemsAttribute($value)
    {
        return json_decode($value);
    }

    public function setItemsAttribute($value)
    {
        $this->attributes['items'] = json_encode($value);
    }
}
