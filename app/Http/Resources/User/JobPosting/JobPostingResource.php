<?php

namespace App\Http\Resources\User\JobPosting;

use App\Services\User\Job\JobService;
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
            'postal_code' => $this['postal_code'],
            'address' => [
                'province' => $this['province'],
                'district' => $this['district'],
                'city' => $this['city'],
                'address' => $this['address'],
            ],
            'job_types' => $this['job_types'],
            'salary' => [
                'min' => $this['salary_min'],
                'max' => $this['salary_max'],
                'type' => $this['salary_type'],
            ],
            'work_time' => [
                'start' => $this['start_work_time'],
                'end' => $this['end_work_time'],
            ],
            'work_types' =>  $this['work_types'],
            'is_favorite' => $this['is_favorite'],
            'released_at' => $this['released_at'],
        ];
    }
}
