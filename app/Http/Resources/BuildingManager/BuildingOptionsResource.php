<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingOptionsResource extends JsonResource
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
            'charge_day' => $this->charge_day,
            'rent_day' => $this->rent_day,
            'custom_payment' => $this->custom_payment,
            'late_fine' => $this->late_fine,
            'late_fine_percent' => $this->late_fine_percent,
            'late_fine_days' => $this->late_fine_days,
            'manual_payment' => $this->manual_payment,
            'auto_add_monthly_charge' => $this->auto_add_monthly_charge,
            'auto_add_monthly_rent' => $this->auto_add_monthly_rent,
            'early_payment' => $this->early_payment,
            'early_payment_percent' => $this->early_payment_percent,
            'early_payment_days' => $this->early_payment_days,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'polls' => $this->polls,
            'send_building_manager_payment_notification' => $this->send_building_manager_payment_notification,
            'separate_resident_and_owner_invoices' => $this->separate_resident_and_owner_invoices,
            'currency' => $this->currency,
            'excel_export' => $this->excel_export,
            'multi_balance' => $this->multi_balance,
            'has_rent' => $this->has_rent,
            'show_costs_to_units' => $this->show_costs_to_units,
            'show_stocks_to_units' => $this->show_stocks_to_units,
            'show_balances_to_units' => $this->show_balances_to_units,
        ];
    }
}
