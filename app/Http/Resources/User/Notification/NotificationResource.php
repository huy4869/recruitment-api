<?php

namespace App\Http\Resources\User\Notification;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'notice_type_id' => $this->notice_type_id,
            'notice_type_name' => @$this->noticeType->name,
            'noti_object_ids' => $this->noti_object_ids,
            'title' => $this->title,
            'content' => $this->content,
            'be_read' => $this->be_read,
            'created_at' => DateTimeHelper::formatTimeNotification($this->created_at),
        ];
    }
}
