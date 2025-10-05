<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function getIncrementedQuantityAttribute()
    {
        return $this->transactions()->where('quantity', '>', 0)->sum('quantity');
    }

    public function getDecrementedQuantityAttribute()
    {
        return -1 * $this->transactions()->where('quantity', '<', 0)->sum('quantity');
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->quantity +  $this->incremented_quantity - $this->decremented_quantity;
    }

    public function getTotalPriceAttribute()
    {
        $price = 0;
        $price += $this->price * $this->quantity;
        foreach ($this->transactions as $transaction) {
            $price += $transaction  ['price'] * $transaction  ['quantity'];
        }
        return $price;
    }
}
