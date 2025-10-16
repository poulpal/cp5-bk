<?php

namespace App\Http\Resources\BuildingManager;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingManagerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'type' => $this->humanReadableType(),
        ];
    }

    private function humanReadableType()
    {
        switch ($this->building_manager_type) {
            case 'main':
                return __("دسترسی کامل");
                break;
            case 'other':
                return __("مشاهده");
                break;
            case 'hsh-1':
                return __("حسابداری");
                break;
            default:
                return __("نامشخص");
                break;
        }
    }
}
