<?php

namespace App\Http\Resources\User\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\MInterviewApproach;
use App\Services\User\Job\JobService;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailJobPostingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $application = $this['applications'][0] ?? [];
        $dataWorkTime = DateTimeHelper::getStartEndWorkTime($this['start_work_time'], $this['end_work_time'], $this['start_work_time_type'], $this['end_work_time_type'], $this['range_hours_type']);

        if (count($application)) {
            switch ($application['interview_approach_id']) {
                case MInterviewApproach::STATUS_INTERVIEW_ONLINE:
                    if ($application['meet_link']) {
                        $approach = $application['meet_link'];
                    } else {
                        $approach = config('application.interview_approach_online');
                    }
                    break;
                case MInterviewApproach::STATUS_INTERVIEW_DIRECT:
                    $postalCode = $this['postal_code'];
                    $province = $this['province'];
                    $provinceCity = $this['province_city'];
                    $address = $this['address'];
                    $building = $this['building'];
                    $approach = sprintf(
                        '〒%s %s%s%s%s',
                        $postalCode,
                        $province,
                        $provinceCity,
                        $address,
                        $building,
                    );
                    break;
                case MInterviewApproach::STATUS_INTERVIEW_PHONE:
                    $approach = $this['store']['owner']['tel'];
                    break;
            }//end switch
        }//end if

        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'store_name' => $this['store_name'],
            'pick_up_point' => $this['pick_up_point'],
            'banner_image' => $this['banner_image'],
            'detail_images' => DetailImageResource::collection($this['detail_images']),
            'job_types' => $this['job_types'],
            'feature_types' => $this['feature_types'],
            'experience_types' => $this['experience_types'],
            'description' => $this['description'],
            'work_types' =>  $this['work_types'],
            'salary' => [
                'min' => $this['salary_min'],
                'max' => $this['salary_max'],
                'type' => $this['salary_type'],
                'description' => $this['salary_description'],
            ],
            'work_time' => [
                'start' => $dataWorkTime['start'],
                'end' => $dataWorkTime['end'],
                'shifts' => $this['shifts'],
            ],
            'age' => [
                'min' => $this['age_min'],
                'max' => $this['age_max'],
            ],
            'genders' =>  $this['genders'],
            'postal_code' => $this['postal_code'],
            'address' => [
                'province_id' => $this['province_id'],
                'province' => $this['province'],
                'province_city_id' => $this['province_city_id'],
                'province_city' => $this['province_city'],
                'address' => $this['address'],
                'building' => $this['building'],
            ],
            'stations' => $this['stations'],
            'welfare_treatment_description' => $this['welfare_treatment_description'],
            'is_favorite' => $this['is_favorite'],
            'is_apply' => $this['is_apply'],
            'application' => $application ? [
                'id' => $application['id'],
                'status' => [
                    'id' => $application['interviews']['id'],
                    'name' => $application['interviews']['name'],
                ],
                'date' => DateTimeHelper::formatDateDayOfWeekJa($application['date']) . $application['hours'],
                'interview_approaches' => [
                    'id' => $application['interview_approach_id'],
                    'method' => $application['interview_approach']['name'],
                    'approach_label' => config('application.interview_approach_label.' . $application['interview_approach_id']),
                    'approach' => $approach,
                ]
            ] : [],
            'is_draft' => $this['job_status_id'] == JobPosting::STATUS_DRAFT,
            'is_release' => $this['job_status_id'] == JobPosting::STATUS_RELEASE,
            'is_end' => $this['job_status_id'] == JobPosting::STATUS_END,
            'is_hide' => $this['job_status_id'] == JobPosting::STATUS_HIDE,
            'released_at' => DateTimeHelper::formatDateJa($this['released_at']),
            'updated_at' => DateTimeHelper::formatDateJa($this['updated_at']),
        ];
    }
}
