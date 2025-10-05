<?php

namespace App\Http\Resources\Public;

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
            'description' => str_replace("\n", "<br>", $this->description),
            'cost_per_hour' => $this->cost_per_hour,
            'available_hours' => $this->available_hours,
            'remaining_hours_this_month' =>  $this->remaining_hours_this_month,
            // 'reservations' => ReservationResource::collection($this->reservations),
            'active_reservations' => ReservationResource::collection($this->active_reservations),
        ];
    }
}
