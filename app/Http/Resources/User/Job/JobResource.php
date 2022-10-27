<?php

namespace App\Http\Resources\User\Job;

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
            'store' => [
                'id' => $this->store->id,
                'name' => $this->store->name,
            ],
            'address' => [
                'postal_code' => $this->postal_code,
                'province_city' => [
                    'id' => $this->provinceCity->id,
                    'name' => $this->provinceCity->name,
                ],
                'province' => [
                    'id' => $this->province->id,
                    'name' => $this->province->name,
                ],
                'district' => [
                    'id' => $this->province->provinceDistrict->id,
                    'name' => $this->province->provinceDistrict->name,
                ],
                'city' => $this->city,
                'address' => $this->address,
            ],
            'job_types' => $this->job_types,
            'work_types' => $this->work_types,
            'salary' => [
                'id' => $this->salaryType->id,
                'name' => $this->salaryType->name,
                'min' => $this->salary_min,
                'max' => $this->salary_max,
            ],
            'work_time' => [
                'start' => $this->start_work_time,
                'end' => $this->end_work_time,
            ],
            'is_favorite' => $this->is_favorite,
            'created_at' => $this->created_at,
        ];
    }
}
