<?php

namespace App\Http\Resources\User\DesiredCondition;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesiredConditionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;
        $salaryMin = $data['salary_min'];
        $salaryMax = $data['salary_max'];
        $salaryTypeName = $data['salary_type']['name'];
        $expectedSalary = sprintf('%s ~ %s%s', $salaryMin, $salaryMax, $salaryTypeName);

        if ($data['province']['name'] == $data['province']['province_district']['name']) {
            $province = $data['province']['name'];
        } else {
            $province = $data['province']['province_district']['name'] . ', ' . $data['province']['name'];
        }

        return [
            'id' => $data['id'],
            'province_id' => $data['province_id'],
            'province' => $province,
            'salary_type_id' => $data['salary_type_id'],
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'expected_salary' => $expectedSalary,
            'age' => $data['age'],
            'age_name' => @config('date.age')[$data['age']],
            'work_type_ids' => $data['work_type_ids'],
            'job_type_ids' => $data['job_type_ids'],
            'job_experience_ids' => $data['job_experience_ids'],
            'job_feature_ids' => $data['job_feature_ids'],
            'work_type_string' => $data['work_type_string'],
            'job_type_string' => $data['job_type_string'],
            'job_experience_strings' => $data['job_experience_strings'],
            'job_feature_string' => $data['job_feature_string'],
        ];
    }
}
