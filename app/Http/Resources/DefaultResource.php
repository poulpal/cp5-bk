<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

class DefaultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        return [
            'id' => $this->id,
            'credit' => $this->credit,
            'debit' => $this->debit,
            'account' => $this->account->name,
            'document' => $this->document->document_number,
            'date' => Jalalian::forge($this->created_at)->format('Y-m-d'),
        ];
    }
}
