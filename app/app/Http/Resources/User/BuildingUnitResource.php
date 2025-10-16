<?php

namespace App\Http\Resources\User;

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
            'resident_type' => $this->resident_type ?? null,
            'charge_fee' => round($this->charge_fee, 1),
            'rent_fee' => round($this->rent_fee, 1),
            'charge_debt' => round($this->charge_debt, 1),
            'resident_debt' => round($this->resident_debt, 1),
            'owner_debt' => round($this->owner_debt, 1),
            'discount' => round($this->discount, 1),
            'token' => $this->token,
            'created_at' => $this->created_at,
            'canPayCustomAmount' => $this->canPayCustomAmount,
            'canPayManual' => $this->canPayManual,
            'separateResidentAndOwnerInvoices' => $this->separateResidentAndOwnerInvoices,
            'building' => BuildingResource::make($this->building),
        ];
    }
}
