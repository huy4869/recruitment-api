<?php

namespace App\Http\Resources\Recruiter\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\JobPosting;
use App\Services\Recruiter\Job\JobService;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $dataWorkTime = DateTimeHelper::getStartEndWorkTime($this->start_work_time, $this->end_work_time, $this->start_work_time_type, $this->end_work_time_type, $this->range_hours_type);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'store_id' => $this->storeTrashed->id,
            'store_name' => $this->storeTrashed->name,
            'company_name' => $this->storeTrashed->owner->company_name,
            'pick_up_point' => $this->pick_up_point,
            'banner_image' => FileHelper::getFullUrl(@$this->bannerImage->url),
            'detail_images' => DetailImageResource::collection($this->detailImages),
            'job_status_id' => $this->job_status_id,
            'job_status_name' => $this->status->name ?? null,
            'statuses' => JobService::getStatusJob(),
            'job_types' => $this->job_types,
            'feature_ids' => array_map('intval', $this->feature_ids ?: []),
            'feature_types' => $this->feature_types,
            'experience_types' => $this->expericence_types,
            'description' => $this->description,
            'work_types' =>  $this->work_types,
            'salary' => [
                'min' => $this->salary_min,
                'max' => $this->salary_max,
                'type_id' => @$this->salaryType->id,
                'type_name' => @$this->salaryType->name,
                'description' =>  $this->salary_description,
            ],
            'work_time' => [
                'start' => $dataWorkTime['start'],
                'start_work_time_type' => $this->start_work_time_type,
                'start_work_time_name' => config('date.day.' . $this->start_work_time_type),
                'start_time' => DateTimeHelper::getHoursMinute($this->start_work_time),
                'end' => $dataWorkTime['end'],
                'end_work_time_type' => $this->end_work_time_type,
                'end_work_time_name' => config('date.day.' . $this->end_work_time_type),
                'end_time' => DateTimeHelper::getHoursMinute($this->end_work_time),
            ],
            'age' => [
                'min' => $this->age_min,
                'max' => $this->age_max,
            ],
            'genders' =>  $this->genders,
            'address' => [
                'postal_code' => $this->postal_code,
                'province_id' => @$this->province->id,
                'province_name' => @$this->province->name,
                'province_city_id' => @$this->provinceCity->id,
                'province_city_name' => @$this->provinceCity->name,
                'address' => $this->address,
                'building' => $this->building,
            ],
            'working_days' => $this->working_days,
            'range_hours_type' => $this->range_hours_type,
            'range_hours_type_name' => $this->range_hours_type == JobPosting::FULL_DAY ? trans('job_posting.range_hours_type.full_day') : trans('job_posting.range_hours_type.half_day'),
            'stations' => $this->stations,
            'shifts' => $this->shifts,
            'welfare_treatment_description' => $this->welfare_treatment_description,
            'released_at' => DateTimeHelper::formatDateJa($this->released_at),
            'updated_at' => DateTimeHelper::formatDateJa($this->updated_at),
            'is_deleted' => !is_null($this->deleted_at),
        ];
    }
}
