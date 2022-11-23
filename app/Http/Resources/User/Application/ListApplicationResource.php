<?php

namespace App\Http\Resources\User\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Application;
use App\Models\MInterviewApproach;
use App\Models\MInterviewStatus;
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
        $isDirectInterview = $data->interview_approach_id == MInterviewApproach::STATUS_INTERVIEW_DIRECT;

        if ($isDirectInterview) {
            $dataApproach = @$data->storeAcceptTrashed->address;
        } elseif ($data->interview_approach_id == MInterviewApproach::STATUS_INTERVIEW_ONLINE) {
            $dataApproach = config('application.interview_approach_online');
        } else {
            $dataApproach = @$data->storeAcceptTrashed->owner->tel;
        }

        $applyOrInterview = in_array($data->interview_status_id, [MInterviewApproach::STATUS_INTERVIEW_ONLINE, MInterviewStatus::STATUS_WAITING_INTERVIEW]);
        $allowEdit =  $data->interview_status_id == MInterviewStatus::STATUS_APPLYING;
        $allowCancel =  !in_array($data->interview_status_id, [MInterviewStatus::STATUS_ACCEPTED, MInterviewStatus::STATUS_CANCELED, MInterviewStatus::STATUS_REJECTED]);

        return [
            'id' => $data->id,
            'job_id' => $data->job_posting_id,
            'job_name' => @$data->jobPostingAcceptTrashed->name,
            'job_banner' => FileHelper::getFullUrl(@$data->jobPostingAcceptTrashed->bannerImageAcceptTrashed->url),
            'store_id' => $data->store_id,
            'store_name' => @$data->storeAcceptTrashed->name,
            'interview_status_id' => $data->interview_status_id,
            'interview_status_name' => @$data->interviews->name,
            'interview_date' => DateTimeHelper::formatDateDayOfWeekJa($data['date']) . $data->hours,
            'apply_or_interview' => $applyOrInterview,
            'allow_edit' => $allowEdit,
            'allow_cancel' => $allowCancel,
            'interview_approach' => [
                'id' => $data->interview_approach_id,
                'method' => $interviewApproaches[$data->interview_approach_id],
                'approach_label' => config('application.interview_approach_label.' . $data->interview_approach_id),
                'approach' => $dataApproach,
                'is_direct_interview' => $isDirectInterview,
            ],
            'submission_date_label' => trans('response.submission_date_label'),
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($data['created_at']),
        ];
    }
}
