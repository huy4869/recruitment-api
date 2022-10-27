<?php

namespace App\Http\Resources\User\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Services\User\ApplicationService;
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
        $interviewApproaches = ApplicationService::interviewApproach();

        return [
            'id' => $data->id,
            'job_id' => $data->job_posting_id,
            'job_name' => $data->jobPosting->name,
            'job_banner' => FileHelper::getFullUrl(@$data->jobPosting->bannerImage->url),
            'store_id' => $data->store_id,
            'store_name' => $data->store->name,
            'interview_status_id' => $data->interview_status_id,
            'interview_status_name' => @$data->interviews->name,
            'date' => DateTimeHelper::formatDateDayOfWeekJa($data['date']) . ' ' . $data->hours,
            'interview_approaches' => [
                'id' => $data->interview_approaches['id'],
                'method' => $interviewApproaches[$data->interview_approaches['id']],
                'approach_label' => config('application.interview_approach_label.' . $data->interview_approaches['id']),
                'approach' => $data->interview_approaches['approach'],
            ],
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($data['created_at']),
        ];
    }
}
