<?php

namespace App\Http\Resources\Admin\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'interview' => [
                'id' => $this->interviews->id,
                'name' => $this->interviews->name,
            ],
            'job' => [
                'id' => $this->job_id,
                'name' => $this->job_name,
            ],
            'user' => [
                'id' => $this->user_id,
                'avatar_banner' => FileHelper::getFullUrl(@$this->applicationUser->avatarBanner->url),
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'furi_first_name' => $this->furi_first_name,
                'furi_last_name' => $this->furi_last_name,
                'age' => $this->age,
            ],
            'be_read' => $this->checked_at && $this->checked_at >= $this->updated_at,
            'created_at' => DateTimeHelper::formatDateDayOfWeekJa($this->created_at),
        ];
    }
}
