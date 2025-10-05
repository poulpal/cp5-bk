<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingUnitWithResidentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $withResidents = $request->withResidents ?? false;
        if ($withResidents) {
            return [
                'id' => $this->id,
                'unit_number' => $this->unit_number,
                'charge_fee' => round($this->charge_fee, 1),
                'rent_fee' => round($this->rent_fee, 1),
                'charge_debt' => round($this->charge_debt, 1),
                'area' => $this->area,
                'resident_count' => $this->resident_count,
                'token' => $this->token,
                'created_at' => $this->created_at,
                'residents' => ResidentResource::collection($this->residents),
                'past_residents' => ResidentResource::collection($this->residentsWithTrashed()->orderBy('building_units_users.created_at', 'desc')->get()),
                'balance' => $this->balance,
            ];
        }
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'charge_fee' => round($this->charge_fee, 1),
            'rent_fee' => round($this->rent_fee, 1),
            'charge_debt' => round($this->charge_debt, 1),
            'resident_debt' => round($this->resident_debt, 1),
            'owner_debt' => round($this->owner_debt, 1),
            'area' => $this->area,
            'resident_count' => $this->resident_count,
            'token' => $this->token,
            'created_at' => $this->created_at,
            'balance' => $this->balance,
            // 'residents' => ResidentResource::collection($this->residents),
            // 'past_residents' => ResidentResource::collection($this->residentsWithTrashed()->orderBy('building_units_users.created_at', 'desc')->get()),
        ];
    }
}
