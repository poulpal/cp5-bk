<?php

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
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
            'is_locked' => $this->is_locked,
            'parent_id' => $this->parent_id,
            'accountable_type' => $this->accountable_type,
            'accountable_id' => $this->accountable_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
