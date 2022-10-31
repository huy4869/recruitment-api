<?php

namespace App\Http\Resources\User;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $jobPosting = $this['job_posting'];

        return [
                'id_favorite' => $this['id'],
                'id' => $this['job_posting_id'],
                'name' => $jobPosting['name'],
                'banner_image' => FileHelper::getFullUrl($jobPosting['banner_image']['url'] ?? null),
                'store_name' => $jobPosting['store']['name'],
                'interview_status' => $jobPosting['applications'][0]['interviews']['name'] ?? null,
                'postal_code' => $jobPosting['postal_code'],
                'address' => [
                    'province_city' => $jobPosting['province_city']['name'] ?? null,
                    'province' => $jobPosting['province']['name'],
                    'district' => $jobPosting['province']['province_district']['name'],
                    'city' => $jobPosting['city'],
                    'address' => $jobPosting['address'],
                ],
                'work_types' => $this['work_types'],
                'job_types' => $this['job_types'],
                'salary' => [
                    'id' => $jobPosting['salary_type']['id'],
                    'type' => $jobPosting['salary_type']['name'],
                    'min' => $jobPosting['salary_min'],
                    'max' => $jobPosting['salary_max'],
                ],
                'work_time' => [
                    'start' => $jobPosting['start_work_time'],
                    'end' => $jobPosting['end_work_time'],
                ],
                'holiday_description' => $jobPosting['holiday_description'],
                'description' => $jobPosting['description'],
                'released_at' => DateTimeHelper::formatDateJa($jobPosting['released_at']),
        ];
    }
}
