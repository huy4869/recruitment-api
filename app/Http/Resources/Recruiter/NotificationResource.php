<?php

namespace App\Http\Resources\Recruiter;

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
        $date = DateTimeHelper::formatYearMonthChat($this->created_at);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'notice_type_id' => $this->notice_type_id,
            'notice_type_name' => $this->noticeType->name ?? null,
            'noti_object_ids' => $this->noti_object_ids,
            'title' => $this->title,
            'content' => $this->content,
            'be_read' => $this->be_read,
            'send_time' => $date,
            'initial_time' => DateTimeHelper::formatDateTimeJa($this->created_at),
        ];
    }
}
