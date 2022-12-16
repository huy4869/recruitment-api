<?php

namespace App\Http\Resources\Admin\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $dataWorkTime = DateTimeHelper::getStartEndWorkTime($this->start_work_time, $this->end_work_time, $this->start_work_time_type, $this->end_work_time_type, $this->range_hours_type);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'banner_image' => FileHelper::getFullUrl(@$this->bannerImage->url),
            'store_name' => $this->store->name,
            'job_status' => [
                'id' => $this->status->id,
                'name' => $this->status->name,
            ],
            'address' => [
                'postal_code' => $this->postal_code,
                'province_id' => $this->province->id,
                'province_name' => @$this->province->name,
                'province_city_id' => $this->province_city_id,
                'province_city_name' => @$this->provinceCity->name,
                'district_id' => $this->province->provinceDistrict->id,
                'district_name' => $this->province->provinceDistrict->name,
                'address' => $this->address,
                'building' => $this->building,
            ],
            'salary' => [
                'type' => $this->salaryType->name,
                'min' => $this->salary_min,
                'max' => $this->salary_max,
            ],
            'work_time' => [
                'start' => $dataWorkTime['start'],
                'end' => $dataWorkTime['end'],
            ],
            'job_types' => $this->job_types,
            'work_types' => $this->work_types,
            'description' => $this->description,
            'created_at' => DateTimeHelper::formatDateJa($this->created_at),
            'updated_at' => DateTimeHelper::formatDateJa($this->updated_at),
        ];
    }
}
