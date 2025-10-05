<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class BuildingManager extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    use Notifiable;
    use SoftDeletes;
    use HasApiTokens;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('role', 'building_manager');
        });
    }

    public function routeNotificationForSms($notifiable)
    {
        return $this->mobile;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }

    public function details()
    {
        return $this->hasOne(Details::class, 'user_id', 'id');
    }

    public function fcm_tokens()
    {
        return $this->hasMany(FcmToken::class, 'user_id', 'id');
    }
}
