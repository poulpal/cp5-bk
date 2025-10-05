<?php

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResrouce extends JsonResource
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
            'building_id' => $this->building_id,
            'name' => __($this->name),
            'code' => $this->code,
            'type' => $this->type,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'is_locked' => $this->is_locked,
            'accountable_type' => $this->accountable_type,
            'accountable_id' => $this->accountable_id,
        ];
    }
}
