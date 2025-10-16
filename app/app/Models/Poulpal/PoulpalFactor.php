<?php

namespace App\Models\Poulpal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoulpalFactor extends Model
{
    use HasFactory;

    protected $connection = 'poulpal';
    protected $table = 'factors';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $uniqid = random_int(100000000, 999999999);
                $exists = self::where('token', $uniqid)->exists();
            } while ($exists);
            $model->token = $uniqid;
        });
    }
}
