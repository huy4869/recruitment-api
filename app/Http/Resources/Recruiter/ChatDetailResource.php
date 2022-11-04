<?php

namespace App\Http\Resources\Recruiter;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $date = DateTimeHelper::formatTimeChat($this['created_at']);

        return [
            'first_name' => @$this['user']['first_name'],
            'last_name' => @$this['user']['last_name'],
            'avatar' => FileHelper::getFullUrl($this['user']['avatarBanner']['url'] ?? null),
            'send_time' => $date,
            'initial_time' => DateTimeHelper::formatDateTimeJa($this->created_at),
            'content' => $this->content,
            'is_from_user' => $this->is_from_user,
            'be_readed' => $this->be_readed,
        ];
    }
}
