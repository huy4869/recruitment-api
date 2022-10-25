<?php

namespace App\Http\Resources\Recruiter\Application;

use App\Helpers\DateTimeHelper;
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
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'furi_first_name' => $this->furi_first_name,
                'furi_last_name' => $this->furi_last_name,
            ],
            'created_at' => DateTimeHelper::formatDateDayOfWeekJa($this->created_at),
        ];
    }
}
