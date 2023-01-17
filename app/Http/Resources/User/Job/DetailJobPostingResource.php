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
        $storeOwner = $this['store_trashed']['owner'];
        $isLink = false;

        if (count($application)) {
            switch ($application['interview_approach_id']) {
                case MInterviewApproach::STATUS_INTERVIEW_ONLINE:
                    if ($application['meet_link']) {
                        $approach = $application['meet_link'];
                        $isLink = true;
                    } else {
                        $approach = config('application.interview_approach_online');
                    }
                    break;
                case MInterviewApproach::STATUS_INTERVIEW_DIRECT:
                    $postalCode = $this['store_trashed']['postal_code'];
                    $province = $this['store_trashed']['province']['name'];
                    $provinceCity = $this['store_trashed']['province_city']['name'];
                    $address = $this['store_trashed']['address'];
                    $building = $this['store_trashed']['building'];
                    $approach = sprintf(
                        '%s %s%s%s%s',
                        $postalCode ? sprintf('〒%s-%s', substr($postalCode, 0, 3), substr($postalCode, -4)) : null,
                        $province,
                        $provinceCity,
                        $address,
                        $building,
                    );
                    break;
                case MInterviewApproach::STATUS_INTERVIEW_PHONE:
                    if ($this['store_trashed']['application_tel']) {
                        $approach = $this['store_trashed']['application_tel'];
                    } else {
                        $approach = $this['store_trashed']['tel'];
                    }

                    if ($approach) {
                        $approach = str_replace('-', '', $approach);
                        $approach = sprintf('%s-%s-%s',
                            substr($approach, 0, 3),
                            substr($approach, 3, 4),
                            substr($approach, 7, strlen($approach) - 7)
                        );
                    }
                    break;
            }//end switch
        }//end if

        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'store_name' => $this['store_trashed']['name'],
            'company_name' => $storeOwner['company_name'],
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
                'postal_code' => $this['postal_code'],
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
                    'is_link' => $isLink
                ]
            ] : [],
            'sns' => [
                'facebook' => $storeOwner['facebook'],
                'twitter' => $storeOwner['twitter'],
                'instagram' => $storeOwner['instagram'],
                'line' => $storeOwner['line'],
            ],
            'is_draft' => $this['job_status_id'] == JobPosting::STATUS_DRAFT,
            'is_release' => $this['job_status_id'] == JobPosting::STATUS_RELEASE,
            'is_end' => $this['job_status_id'] == JobPosting::STATUS_END,
            'is_hide' => $this['job_status_id'] == JobPosting::STATUS_HIDE,
            'released_at' => DateTimeHelper::formatDateJa($this['released_at']),
            'updated_at' => DateTimeHelper::formatDateJa($this['updated_at']),
        ];
    }
}
