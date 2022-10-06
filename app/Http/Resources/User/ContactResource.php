<?php

namespace App\Http\Resources\User;

use App\Helpers\DateTimeHelper;
use Carbon\Traits\Date;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'user_id' => $this->user_id,
            'email' => $this->email,
            'name' => $this->name,
            'tel' => $this->tel,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'created_at_ja' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
