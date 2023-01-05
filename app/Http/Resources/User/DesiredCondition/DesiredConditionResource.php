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
        $startHoursWorking = '';
        $salaryMin = @$data['salary_min'];
        $salaryMax = @$data['salary_max'];
        $startWorkingHours = substr($data['start_working_time'], 0, 2);
        $startWorkingMinutes = substr($data['start_working_time'], 2);
        $endWorkingHours = substr($data['end_working_time'], 0, 2);
        $endWorkingMinutes = substr($data['end_working_time'], 2);
        $salaryTypeName = @$data['salaryType']['name'];

        if ($salaryMin && !$salaryMax) {
            $expectedSalary = sprintf('%s～%s', $salaryMin, $salaryTypeName);
        } elseif (!$salaryMin && $salaryMax) {
            $expectedSalary = sprintf('～%s%s', $salaryMax, $salaryTypeName);
        } elseif ($salaryMin && $salaryMax) {
            $expectedSalary = sprintf('%s～%s%s', $salaryMin, $salaryMax, $salaryTypeName);
        } else {
            $expectedSalary = '';
        }

        if ($data['start_working_time'] && $data['end_working_time']) {
            $startWorkingTimes = $startWorkingHours . ':' . $startWorkingMinutes;
            $endWorkingTimes = $endWorkingHours . ':' . $endWorkingMinutes;
            $startHoursWorking = $startWorkingTimes . '～' . $endWorkingTimes;
        }
        $age = @config('user.age')[$data['age']] ? @config('user.age')[$data['age']] . '代以上' : null;

        return [
            'id' => $data['id'],
            'province_ids' => array_map('intval', $data['province_ids'] ?: []),
            'list_province' => $data['list_province'],
            'salary_type_id' => $data['salary_type_id'],
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'expected_salary' => $expectedSalary,
            'age_id' => $data['age'],
            'age_name' => $age,
            'work_type_ids' => array_map('intval', $data['work_type_ids'] ?: []),
            'job_type_ids' => array_map('intval', $data['job_type_ids'] ?: []),
            'job_experience_ids' => array_map('intval', $data['job_experience_ids'] ?: []),
            'job_feature_ids' => array_map('intval', $data['job_feature_ids'] ?: []),
            'work_type_string' => $data['work_type_string'],
            'working_hours' => [
                'start_hours' => $startWorkingHours,
                'start_minutes' => $startWorkingMinutes,
                'end_hours' => $endWorkingHours,
                'end_minutes' => $endWorkingMinutes,
                'working_hours_format' => $startHoursWorking,
            ],
            'working_days' => array_map('intval', $data['working_days'] ?: []),
            'job_type_string' => $data['job_type_string'],
            'job_experience_strings' => $data['job_experience_strings'],
            'job_feature_string' => $data['job_feature_string'],
        ];
    }
}
