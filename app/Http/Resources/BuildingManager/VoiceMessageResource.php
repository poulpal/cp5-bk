<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class VoiceMessageResource extends JsonResource
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
            'pattern' => $this->pattern,
            'status' => $this->status,
            'units' => $this->units,
            'length' => $this->length,
            'count' => $this->count,
            'scheduled_at' => $this->scheduled_at,
            'batch' => $this->batch,
            'created_at' => $this->created_at,
        ];
    }
}
