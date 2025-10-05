<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
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
            'slug' => $this->slug,
            'title' => $this->title,
            'type' => $this->type,
            'price' => $this->price,
            'order' => $this->order,
            // 'features' => $this->features,
            'is_on_offer' => $this->is_on_offer,
            'offer_before_price' => $this->offer_before_price,
            'offer_description' => $this->offer_description,
            'description' => $this->description,
            'ends_at' => $this->pivot->ends_at ?? null,
        ];
    }
}
