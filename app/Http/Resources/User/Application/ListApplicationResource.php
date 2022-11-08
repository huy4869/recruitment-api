<?php

namespace App\Http\Resources\User\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Application;
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
        $isDirectInterview = $data->interview_approaches['id'] == Application::STATUS_INTERVIEW_DIRECT;

        if ($isDirectInterview) {
            $dataApproach = @$data->store->address;
        } elseif ($data->interview_approaches['id'] == Application::STATUS_INTERVIEW_ONLINE) {
            $dataApproach = '2営業日以内にZoomURLをメールにて送付いたします';
        } else {
            $dataApproach = @$data->store->owner->tel;
        }

        $applyOrInterview = $data->interview_status_id != Application::STATUS_CANCELED;
        $allowEdit =  $data->interview_status_id == Application::STATUS_APPLYING;
        $allowCancel =  !in_array($data->interview_status_id, [Application::STATUS_ACCEPTED, Application::STATUS_CANCELED, Application::STATUS_REJECTED]);

        return [
            'id' => $data->id,
            'job_id' => $data->job_posting_id,
            'job_name' => $data->jobPosting->name,
            'job_banner' => FileHelper::getFullUrl(@$data->jobPosting->bannerImage->url),
            'store_id' => $data->store_id,
            'store_name' => $data->store->name,
            'interview_status_id' => $data->interview_status_id,
            'interview_status_name' => @$data->interviews->name,
            'interview_date' => DateTimeHelper::formatDateDayOfWeekJa($data['date']) . $data->hours,
            'apply_or_interview' => $applyOrInterview,
            'allow_edit' => $allowEdit,
            'allow_cancel' => $allowCancel,
            'interview_approach' => [
                'id' => $data->interview_approaches['id'],
                'method' => $interviewApproaches[$data->interview_approaches['id']],
                'approach_label' => config('application.interview_approach_label.' . $data->interview_approaches['id']),
                'approach' => $dataApproach,
                'is_direct_interview' => $isDirectInterview,
            ],
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($data['created_at']),
        ];
    }
}
