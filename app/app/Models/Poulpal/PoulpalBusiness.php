<?php

namespace App\Models\Poulpal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoulpalBusiness extends Model
{
    use HasFactory;

    protected $connection = 'poulpal';
    protected $table = 'businesses';
    protected $guarded = [];
}
