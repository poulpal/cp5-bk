<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProformaItem extends Model
{
    protected $fillable = [
        'proforma_invoice_id','title','description','qty','unit_price','line_total','meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function proforma()
    {
        return $this->belongsTo(ProformaInvoice::class, 'proforma_invoice_id');
    }
}
