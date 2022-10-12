<?php

namespace App\Http\Resources\User\Job;

use Illuminate\Http\Resources\Json\JsonResource;

class JobFavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $work_types = json_decode($this->work_type_ids);
        $job_types = json_decode($this->job_type_ids);

        return [
            'favoriteId' => $this->id,
            'job_name' => $this->job_name,
            'store_name' => $this->store_name,
            'interview_status' => $this->interview_name ?? '',
            'postal_code' => $this->postal_code,
            'province' => $this->province_name,
            'city' => $this->city,
            'address' => $this->address,
            'job_type' => $job_types ?? [],
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'salary_type' => $this->salary_name,
            'start_work_time' => $this->start_work_time,
            'end_work_time' => $this->end_work_time,
            'holiday_description' => $this->holiday_description,
            'description' => $this->description,
            'work_types' => $work_types ?? [],
        ];
    }
}
