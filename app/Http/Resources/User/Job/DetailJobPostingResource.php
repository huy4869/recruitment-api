<?php

namespace App\Http\Resources\User\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Application;
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

        if (count($application)) {
            switch ($application['interview_approach_id']) {
                case Application::STATUS_INTERVIEW_ONLINE:
                    $approach = config('application.interview_approach_online');
                    break;
                case Application::STATUS_INTERVIEW_DIRECT:
                    $postalCode = $this['postal_code'];
                    $province = $this['province'];
                    $provinceCity = $this['province_city']['name'];
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
                case Application::STATUS_INTERVIEW_PHONE:
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
            ],
            'work_time' => [
                'start' => JobHelper::makeWorkTimeFormat($this['start_work_time']),
                'end' => JobHelper::makeWorkTimeFormat($this['end_work_time']),
                'shifts' => $this['shifts'],
            ],
            'age' => [
                'min' => $this['age_min'],
                'max' => $this['age_max'],
            ],
            'genders' =>  $this['genders'],
            'postal_code' => $this['postal_code'],
            'address' => [
                'province' => $this['province'],
                'district' => $this['district'],
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
                'date' => DateTimeHelper::formatDateDayOfWeekTimeJa($application['date']),
                'interview_approaches' => [
                    'id' => $application['interview_approach_id'],
                    'method' => $application['interview_approach']['name'],
                    'approach_label' => config('application.interview_approach_label.' . $application['interview_approach_id']),
                    'approach' => $approach,
                ]
            ] : [],
            'released_at' => DateTimeHelper::formatDateJa($this['released_at']),
            'updated_at' => DateTimeHelper::formatDateJa($this['updated_at']),
        ];
    }
}
