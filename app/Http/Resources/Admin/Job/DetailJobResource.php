<?php

namespace App\Http\Resources\Admin\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Services\Admin\Job\JobService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'store_name' => $this->store->name,
            'pick_up_point' => $this->pick_up_point,
            'banner_image' => FileHelper::getFullUrl(@$this->bannerImage->url),
            'detail_images' => DetailImageResource::collection($this->detailImages),
            'statuses' => JobService::getStatusJob($this->job_status_id),
            'job_types' => $this->job_types,
            'feature_types' => $this->feature_types,
            'experience_types' => $this->expericence_types,
            'description' => $this->description,
            'work_types' =>  $this->work_types,
            'salary' => [
                'min' => $this->salary_min,
                'max' => $this->salary_max,
                'type' => $this->salaryType->name,
            ],
            'work_time' => [
                'working_days' => $this->working_days,
                'start' => $this->start_work_time,
                'end' => $this->end_work_time,
            ],
            'age' => [
                'min' => $this->age_min,
                'max' => $this->age_max,
            ],
            'genders' =>  $this->genders,
            'address' => [
                'postal_code' => $this->postal_code,
                'district_id' => $this->province->id,
                'province_city_id' => $this->province_city_id,
                'province_city_name' => @$this->provinceCity->name,
                'district_name' => @$this->province->name,
                'province_id' => @$this->province->provinceDistrict->id,
                'province_name' => @$this->province->provinceDistrict->name,
                'building' => $this['building'],
                'address' => $this['address'],
            ],
            'stations' => $this->stations,
            'welfare_treatment_description' => $this['welfare_treatment_description'],
            'released_at' => DateTimeHelper::formatDateJa($this['released_at']),
            'updated_at' => DateTimeHelper::formatDateJa($this['updated_at']),
        ];
    }
}
