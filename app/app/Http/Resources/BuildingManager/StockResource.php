<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            // 'building_id' => (int) $this->building_id,
            'title' => $this->title,
            'description' => $this->description,
            'invoice_number' => $this->invoice_number,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'incremented_quantity' => $this->incremented_quantity,
            'decremented_quantity' => $this->decremented_quantity,
            'price' => $this->price,
            'total_price' => $this->total_price,
            'buyer' => $this->buyer,
            'seller' => $this->seller,
            'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];
    }
}
