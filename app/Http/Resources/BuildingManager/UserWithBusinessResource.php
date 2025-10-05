<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWithBusinessResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'role' => $this->role,
            'business' => new BusinessResource($this->details)
        ];
    }
}
