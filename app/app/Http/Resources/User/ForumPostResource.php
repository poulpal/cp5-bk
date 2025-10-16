<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

class ForumPostResource extends JsonResource
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
            'user' => $this->user->full_name,
            'content' => str_replace("\n", "<br>", $this->content),
            'image' => $this->image ? asset($this->image) : null,
            'likes' => $this->likes,
            'date' => Jalalian::fromCarbon($this->created_at)->ago(),
            'is_liked' => $this->isLikedBy(auth()->user()),
        ];
    }
}
