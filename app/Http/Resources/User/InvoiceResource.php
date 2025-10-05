<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->serviceable_type == 'App\Models\BuildingUnit') {
            return [
                'id' => $this->id,
                'amount' => round($this->amount, 1),
                'status' => $this->status,
                'payment_method' => $this->humanReadablePaymentMethod(),
                'card_number' => $this->payment_card_number,
                'trace_number' => $this->payment_tracenumber,
                'description' => $this->description,
                'unit' => BuildingUnitResource::make($this->service),
                'created_at' => $this->created_at,
                'is_verified' => (bool) $this->is_verified,
                'attachments' => AttachmentResource::collection($this->attachments),
            ];
        } else {
            return [
                'id' => $this->id,
                'amount' => round($this->amount, 1),
                'status' => $this->status,
                'payment_method' => $this->humanReadablePaymentMethod(),
                'card_number' => $this->payment_card_number,
                'trace_number' => $this->payment_tracenumber,
                'description' => $this->description,
                'created_at' => $this->created_at,
            ];
        }
    }

    private function humanReadablePaymentMethod()
    {
        switch ($this->payment_method) {
            case 'Shetabit\Multipay\Drivers\Local\Local':
                return __("پرداخت آنلاین");
                break;
            case 'Shetabit\\Multipay\\Drivers\\Sepehr\\Sepehr':
                return __("پرداخت آنلاین");
                break;
            case 'App\\Helpers\\InopayDriver':
                return __("پرداخت آنلاین");
                break;
            case 'wallet':
                return __("پرداخت از کیف پول");
                break;
            case 'cash':
                return __("پرداخت نقدی");
                break;
            default:
                return __("پرداخت آنلاین");
                break;
        }
    }
}
