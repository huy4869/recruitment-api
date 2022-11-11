<?php

namespace App\Http\Resources\User\Job;

use App\Helpers\DateTimeHelper;
use App\Helpers\JobHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostingResource extends JsonResource
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
            'id' => $this['id'],
            'name' => $this['name'],
            'store_name' => $this['store_name'],
            'banner_image' => $this['banner_image'],
            'postal_code' => $this['postal_code'],
            'address' => [
                'province' => $this['province'],
                'district' => $this['district'],
                'address' => $this['address'],
                'building' => $this['building'],
            ],
            'job_types' => $this['job_types'],
            'salary' => [
                'min' => $this['salary_min'],
                'max' => $this['salary_max'],
                'type' => $this['salary_type'],
            ],
            'work_time' => [
                'start' => JobHelper::makeWorkTimeFormat($this['start_work_time']),
                'end' => JobHelper::makeWorkTimeFormat($this['end_work_time']),
            ],
            'work_types' =>  $this['work_types'],
            'is_favorite' => $this['is_favorite'],
            'is_new' => JobHelper::isNew($this['released_at']),
            'released_at' => DateTimeHelper::formatDateJa($this['released_at']),
        ];
    }
}
