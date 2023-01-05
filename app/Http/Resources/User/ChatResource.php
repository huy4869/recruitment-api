<?php

namespace App\Http\Resources\User;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Chat;
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
        $date = DateTimeHelper::formatYearMonthChat($this->created_at);
        $beRead = $this->be_readed;
        if ($this->is_from_user == Chat::FROM_USER['TRUE']) {
            $beRead = Chat::BE_READED;
        }

        return [
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'store_name' => $this->storeTrashed->name,
            'is_delete_store' => !!$this->storeTrashed->deleted_at,
            'store_banner' => FileHelper::getFullUrl($this->storeTrashed->storeBanner->url ?? null),
            'send_time' => $date,
            'initial_time' => DateTimeHelper::formatDateTimeJa($this->created_at),
            'content' => $this->content,
            'be_readed' => $beRead,
            'be_deleted' => !!$this->storeTrashed->deleted_at,
        ];
    }
}
