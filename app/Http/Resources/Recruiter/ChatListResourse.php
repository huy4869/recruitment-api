<?php

namespace App\Http\Resources\Recruiter;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Chat;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatListResourse extends JsonResource
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
        $beRead = $this->be_readed;

        if ($this->is_from_user == Chat::FROM_USER['FALSE']) {
            $beRead = Chat::BE_READED;
        }

        return [
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'store_name' => $this->store->name,
            'first_name' => $this->userTrashed->first_name,
            'last_name' => $this->userTrashed->last_name,
            'is_deleted_user' => !!$this->userTrashed->deleted_at,
            'avatar' => FileHelper::getFullUrl($this->userTrashed->avatarBanner->url ?? null),
            'send_time' => $date,
            'initial_time' => DateTimeHelper::formatDateTimeJa($this->created_at),
            'is_from_user' => $this->is_from_user,
            'content' => $this->content,
            'be_read' => $beRead,
            'be_deleted' => !!$this->userTrashed->deleted_at,
        ];
    }
}
