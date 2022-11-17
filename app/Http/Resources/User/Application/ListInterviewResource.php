<?php

namespace App\Http\Resources\User\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Application;
use App\Models\MInterviewApproach;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ListInterviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        switch ($this->interview_approach_id) {
            case Application::STATUS_INTERVIEW_ONLINE:
                $approach = config('application.interview_approach_online');
                break;
            case Application::STATUS_INTERVIEW_DIRECT:
                $postalCode = @$this->jobPostingAcceptTrashed->postal_code;
                $province = @$this->jobPostingAcceptTrashed->province->name;
                $provinceCity = @$this->jobPostingAcceptTrashed->provinceCity->name;
                $address = $this->address;
                $building = $this->building;
                $approach = sprintf(
                    '〒%s %s%s%s%s',
                    $postalCode,
                    $province,
                    $provinceCity,
                    $address,
                    $building,
                );
                break;
            case Application::STATUS_INTERVIEW_PHONE:
                $approach = $this->storeAcceptTrashed->owner->tel;
                break;
        }//end switch

        return [
            'id' => $this->id,
            'job_id' => @$this->jobPostingAcceptTrashed->id,
            'job_banner' => FileHelper::getFullUrl(@$this->jobPostingAcceptTrashed->bannerImageAcceptTrashed->url),
            'job_name' => @$this->jobPostingAcceptTrashed->name,
            'store_name' => @$this->storeAcceptTrashed->name,
            'interview_date' => DateTimeHelper::formatDateDayOfWeekJa($this->date) . $this->hours,
            'interview_date_status' => $this->date_status,
            'interview_approach' => [
                'id' => $this->interview_approach_id,
                'method' => $this->interviewApproach->name,
                'approach_label' => config('application.interview_approach_label.' . $this->interview_approach_id),
                'approach' => $approach,
            ],
            'allow_edit' => $this->can_change_interview,
            'allow_cancel' => $this->can_cancel,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
