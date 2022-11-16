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
        $expectedSalary = '';
        $startHoursWorking = '';
        $salaryMin = @$data['salary_min'];
        $salaryMax = @$data['salary_max'];
        $startWorkingHours = substr($data['start_working_time'], 0, 2);
        $startWorkingMinutes = substr($data['start_working_time'], 2);
        $endWorkingHours = substr($data['end_working_time'], 0, 2);
        $endWorkingMinutes = substr($data['end_working_time'], 2);
        $salaryTypeName = @$data['salary_type']['name'];

        if ($salaryMin && $salaryMax) {
            $expectedSalary = sprintf('%s ~ %s%s', $salaryMin, $salaryMax, $salaryTypeName);
        }

        if ($data['start_working_time'] && $data['end_working_time']) {
            $startWorkingTimes = $startWorkingHours . ':' . $startWorkingMinutes;
            $endWorkingTimes = $endWorkingHours . ':' . $endWorkingMinutes;
            $startHoursWorking = $startWorkingTimes . ' ~ ' . $endWorkingTimes;
        }

        return [
            'id' => $data['id'],
            'province_ids' => $data['province_ids'],
            'list_province' => $data['list_province'],
            'salary_type_id' => $data['salary_type_id'],
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'expected_salary' => $expectedSalary,
            'age_id' => $data['age'],
            'age_name' => @config('date.age')[$data['age']],
            'work_type_ids' => $data['work_type_ids'],
            'job_type_ids' => $data['job_type_ids'],
            'job_experience_ids' => $data['job_experience_ids'],
            'job_feature_ids' => $data['job_feature_ids'],
            'work_type_string' => $data['work_type_string'],
            'working_hours' => [
                'start_hours' => $startWorkingHours,
                'start_minutes' => $startWorkingMinutes,
                'end_hours' => $endWorkingHours,
                'end_minutes' => $endWorkingMinutes,
                'working_hours_format' => $startHoursWorking,
            ],
            'working_days' => $data['working_days'],
            'job_type_string' => $data['job_type_string'],
            'job_experience_strings' => $data['job_experience_strings'],
            'job_feature_string' => $data['job_feature_string'],
        ];
    }
}
