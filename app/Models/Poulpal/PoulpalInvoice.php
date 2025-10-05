<?php

namespace App\Models\Poulpal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoulpalInvoice extends Model
{
    use HasFactory;

    protected $connection = 'poulpal';
    protected $table = 'invoices';
    protected $guarded = [];
}
