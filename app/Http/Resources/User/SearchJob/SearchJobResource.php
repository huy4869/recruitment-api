<?php

namespace App\Http\Resources\User\SearchJob;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchJobResource extends JsonResource
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
            'content' => $this->content,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
