<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class DepositRequestResource extends JsonResource
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
            'amount' => round($this->amount, 1),
            'deposit_to' => $this->deposit_to,
            'sheba' => $this->sheba,
            'description' => $this->description,
            'status' => $this->humanReadableStatus(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'balance' => $this->balance,
        ];
    }

    private function humanReadableStatus()
    {
        switch ($this->status) {
            case 'pending':
                return __("در انتظار تایید");
            case 'accepted':
                return __("تایید شده");
            case 'rejected':
                return __("رد شده");
            default:
                return __("نامشخص");
        }
    }
}
