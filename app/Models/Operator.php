<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Operator extends Authenticatable
{
    use HasFactory;
    

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'password',
    ];

    public function canManageBinshopsBlogPosts()
    {
        return true;
        if ($this->mobile == "09123456789" || $this->mobile == "09360001376") {
            return true;
        }
    }
}
