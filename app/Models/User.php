<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes;
    use HasApiTokens;
    // protected $guard = 'user';


    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        // 'otp',
        'otp_expire_at',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function routeNotificationForSms($notifiable)
    {
        return $this->mobile;
    }

    public function building_units()
    {
        return $this->belongsToMany(BuildingUnit::class, 'building_units_users', 'user_id', 'building_unit_id')
            ->whereNull('building_units_users.deleted_at')
            ->withPivot(['id', 'ownership', 'created_at', 'updated_at', 'deleted_at']);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referall::class);
    }

    public function details()
    {
        return $this->hasOne(Details::class, 'user_id', 'id');
    }

    public function fcm_tokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function canManageBinshopsBlogPosts()
    {
        if ($this->mobile == "09123456789" || $this->mobile == "09360001376") {
            return true;
        }
    }
}
