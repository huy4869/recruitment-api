<?php

namespace App\Http\Resources\Recruiter\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'pick_up_point' => $this->pick_up_point,
            'banner_image' => FileHelper::getFullUrl(@$this->bannerImage->url),
            'detail_images' => DetailImageResource::collection($this->detailImages),
            'job_types' => $this->job_types,
            'feature_ids' => $this->feature_ids,
            'feature_types' => $this->feature_types,
            'experience_types' => $this->expericence_types,
            'description' => $this->description,
            'work_types' =>  $this->work_types,
            'salary' => [
                'min' => $this->salary_min,
                'max' => $this->salary_max,
                'type_id' => $this->salaryType->id,
                'type_name' => $this->salaryType->name,
            ],
            'work_time' => [
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
                'district_name' => $this->province->name,
                'province_id' => $this->province->provinceDistrict->id,
                'province_name' => $this->province->provinceDistrict->name,
                'province_city_id' => $this->provinceCity->id,
                'province_city_name' => $this->provinceCity->name,
                'address' => $this['address'],
                'building' => $this['building'],
            ],
            'working_days' => $this->working_days,
            'stations' => $this->stations,
            'welfare_treatment_description' => $this['welfare_treatment_description'],
            'released_at' => DateTimeHelper::formatDateJa($this['released_at']),
            'updated_at' => DateTimeHelper::formatDateJa($this['updated_at']),
        ];
    }
}
