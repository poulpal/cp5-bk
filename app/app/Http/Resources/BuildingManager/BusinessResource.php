<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
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
            'name' => $this->owner->building->name,
            'name_en' => $this->owner->building->name_en,
            'phone_number' => $this->phone_number,
            'national_id' => $this->national_id,
            'type' => $this->type,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'email' => $this->email,
            'sheba_number' => $this->sheba_number,
            'card_number' => $this->card_number,
            'national_card_image' => $this->national_card_image ? url($this->national_card_image) : null,
            'balance' => $this->owner->building->balance,
            'is_verified' => (bool) $this->is_verified,
            'created_at' => $this->created_at,
        ];
    }
}
