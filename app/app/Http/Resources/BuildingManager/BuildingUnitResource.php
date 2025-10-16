<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingUnitResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'ownership' => $this->pivot->ownership ?? null,
            'charge_fee' => round($this->charge_fee, 1),
            'rent_fee' => round($this->rent_fee, 1),
            'charge_debt' => round($this->charge_debt, 1),
            'area' => $this->area,
            'resident_count' => $this->resident_count,
            'token' => $this->token,
            'created_at' => $this->created_at,
            'building' => BuildingResource::make($this->building),
        ];
    }
}
