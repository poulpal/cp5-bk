<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProformaInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','building_id','package_id',
        'proforma_number','period','subtotal','discount','tax','total','currency',
        'status','issued_at','expires_at','buyer_meta','seller_meta','meta'
    ];

    protected $casts = [
        'issued_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'buyer_meta'  => 'array',
        'seller_meta' => 'array',
        'meta'        => 'array',
    ];

    public function items()
    {
        return $this->hasMany(ProformaItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function nextNumber(): string
    {
        $prefix = 'PF-'.now()->format('Ym').'-';
        $seq = (self::where('proforma_number', 'like', $prefix.'%')->count() + 1);
        return $prefix . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
    }
}
