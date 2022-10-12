<?php

namespace App\Http\Resources\User;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $date = DateTimeHelper::formatTimeChat($this->created_at);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'store_name' => $this->store->name,
            'send_time' => $date,
            'initial_time' => DateTimeHelper::formatDateTimeJa($this->created_at),
            'content' => $this->content,
            'be_readed' => $this->be_readed,
        ];
    }
}