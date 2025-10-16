<?php

namespace App\Http\Resources\BuildingManager;

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
                'amount' =>  round($this->amount, 1),
                'status' => $this->status,
                'payment_method' => $this->humanReadablePaymentMethod(),
                'card_number' => $this->payment_card_number,
                'trace_number' => $this->payment_tracenumber,
                'description' => $this->description,
                'resident_type' => $this->resident_type,
                'debtType' => DebtTypeResource::make($this->debtType),
                'bank' => $this->bank?->name ?? null,
                'unit' => BuildingUnitResource::make($this->service()->withTrashed()->first()),
                'is_verified' => (bool) $this->is_verified,
                'balance' => $this->balance,
                'attachments' => AttachmentResource::collection($this->attachments),
                'created_at' => $this->created_at,
            ];
        } else {
            return [
                'id' => $this->id,
                'amount' =>  round($this->amount, 1),
                'status' => $this->status,
                'payment_method' => $this->humanReadablePaymentMethod(),
                'card_number' => $this->payment_card_number,
                'trace_number' => $this->payment_tracenumber,
                'description' => $this->description,
                'balance' => $this->balance,
                'show_units' => $this->show_units,
                'created_at' => $this->created_at,
            ];
        }
    }

    private function humanReadablePaymentMethod()
    {
        switch ($this->payment_method) {
            case 'Shetabit\\Multipay\\Drivers\\Payir\\Payir':
                return __("پرداخت آنلاین");
                break;
            case 'Shetabit\\Multipay\\Drivers\\Sepehr\\Sepehr':
                return __("پرداخت آنلاین");
                break;
            case 'App\\Helpers\\InopayDriver':
                return __("پرداخت آنلاین");
                break;
            case 'wallet':
                return __("پرداخت آنلاین");
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
