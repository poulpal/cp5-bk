<?php

namespace App\Models\Poulpal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PoulpalBusinessManager extends Model
{
    use HasFactory;

    protected $connection = 'poulpal';
    protected $table = 'users';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('role', 'businessManager');
        });
    }

    public function business()
    {
        return $this->hasOne(PoulpalBusiness::class, 'id', 'business_id');
    }
}
