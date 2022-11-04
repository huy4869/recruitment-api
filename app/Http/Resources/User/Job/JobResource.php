<?php

namespace App\Http\Resources\User\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
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
            'banner_image' => FileHelper::getFullUrl($this->bannerImage->url),
            'store_name' => $this->store->name,
            'postal_code' => $this->postal_code,
            'address' => [
                'province_city' => $this->provinceCity->name,
                'province' => $this->province->name,
                'district' => $this->province->provinceDistrict->name,
                'city' => $this->city,
                'address' => $this->address,
            ],
            'job_types' => $this->job_types,
            'work_types' => $this->work_types,
            'salary' => [
                'id' => $this->salaryType->id,
                'type' => $this->salaryType->name,
                'min' => $this->salary_min,
                'max' => $this->salary_max,
            ],
            'work_time' => [
                'start' => JobHelper::makeWorkTimeFormat($this->start_work_time),
                'end' => JobHelper::makeWorkTimeFormat($this->end_work_time),
            ],
            'is_favorite' => $this->is_favorite,
            'is_new' => JobHelper::isNew($this->released_at),
            'released_at' => DateTimeHelper::formatDateJa($this->released_at),
            'created_at' => DateTimeHelper::formatDateJa($this->created_at),
            'updated_at' => DateTimeHelper::formatDateJa($this->updated_at),
        ];
    }
}
