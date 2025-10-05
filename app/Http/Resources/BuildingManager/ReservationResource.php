<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'id' => (int)$this->id,
            'user' => [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'mobile' => $this->user->mobile
            ],
            'unit' => [
                'unit_number' => $this->unit?->unit_number,
            ],
            'start_time' => $this->start_time->format('Y-m-d H:i:s'),
            'end_time' => $this->end_time->format('Y-m-d H:i:s'),
            'cost' => $this->cost,
            'status' => $this->status,
        ];
    }
}
