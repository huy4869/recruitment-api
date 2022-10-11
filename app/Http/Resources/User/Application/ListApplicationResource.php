<?php

namespace App\Http\Resources\User\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\MInterviewStatus;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;

        return [
            'id' => $data['id'],
            'job_id' => $data['job_posting_id'],
            'job_name' => $data['job_posting']['name'],
            'job_banner' => FileHelper::getFullUrl($data['job_posting']['banner_image']['url']),
            'store_id' => $data['store_id'],
            'store_name' => $data['store']['name'],
            'interview_status_id' => $data['interview_status_id'],
            'interview_status_name' =>$data['interviews']['name'],
            'date' => DateTimeHelper::formatDateDayOfWeekTimeJa($data['date']),
            'interview_approaches_status_name' => $data['interview_approaches_status_name'],
            'interview_approaches_id' => $data['interview_approaches_id'],
            'interview_approaches_name' => $data['interview_approaches_name'],
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($data['created_at']),
        ];
    }
}
