<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
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
            'title' => $this->title,
            'text' => str_replace("\n", "<br>", $this->text),
            'is_public' => $this->is_public,
            'created_at' => $this->created_at,
        ];
    }
}
