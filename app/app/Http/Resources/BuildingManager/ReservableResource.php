<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservableResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'resident_type' => $this->resident_type,
            'monthly_hour_limit' => $this->monthly_hour_limit,
            'cancel_hour_limit' => $this->cancel_hour_limit,
            'cost_per_hour' => $this->cost_per_hour,
            'available_hours' => $this->available_hours,
            'active_reservations_count' => $this->active_reservations_count,
            'created_at' => $this->created_at,
            'reservations' => ReservationResource::collection($this->reservations->sortByDesc('start_time')),
        ];
    }
}
