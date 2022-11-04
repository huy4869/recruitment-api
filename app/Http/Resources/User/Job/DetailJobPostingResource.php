<?php

namespace App\Http\Resources\User\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
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
        $interviewMethod = JobService::getInterviewMethodName();

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
                'city' => $this['city'],
                'address' => $this['address'],
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
                    'id' => $application['interview_approaches']['id'],
                    'method' => $interviewMethod[$application['interview_approaches']['id'] - 1],
                    'approach_label' => config('application.interview_approach_label.' . $application['interview_approaches']['id']),
                    'approach' => $application['interview_approaches']['approach'],
                ]
            ] : [],
            'released_at' => DateTimeHelper::formatDateJa($this['released_at']),
            'updated_at' => DateTimeHelper::formatDateJa($this['updated_at']),
        ];
    }
}
