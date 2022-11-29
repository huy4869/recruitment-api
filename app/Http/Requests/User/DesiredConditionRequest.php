<?php

namespace App\Http\Requests\User;

use App\Models\MJobType;
use App\Models\MWorkType;
use App\Rules\User\CheckSalaryRule;
use App\Services\User\DesiredConditionService;
use App\Services\User\WorkHistoryService;
use Illuminate\Foundation\Http\FormRequest;

class DesiredConditionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $ageType = array_keys(config('user.age'));
        $jobTypeIds = WorkHistoryService::getInstance()->getTypeIds(MJobType::query());
        $workTypeIds = WorkHistoryService::getInstance()->getTypeIds(MWorkType::query());
        $dayIds = array_keys(config('date.day_of_week_ja_fe'));
        $provinceIds = DesiredConditionService::province();

        return [
            'province_ids' => ['nullable', 'array'],
            'province_ids.*' => ['integer', 'in:' . implode(',', $provinceIds)],
            'work_type_ids' => ['nullable', 'array'],
            'work_type_ids.*' => ['integer', 'in:' . implode(',', $workTypeIds)],
            'age_id' => ['nullable', 'numeric', 'in:' . implode(',', $ageType)],
            'salary_type_id' => ['nullable', 'numeric', 'exists:m_salary_types,id'],
            'salary_min' => ['nullable', 'numeric', 'between:0,' . config('validate.salary_max_value')],
            'salary_max' => ['nullable', 'numeric', 'between:0,' . config('validate.salary_max_value'), new CheckSalaryRule()],
            'job_type_ids' => ['nullable', 'array'],
            'job_type_ids.*' => ['integer', 'in:' . implode(',', $jobTypeIds)],
            'job_experience_ids' => ['nullable', 'array'],
            'job_experience_ids.*' => ['integer', 'exists:m_job_experiences,id'],
            'job_feature_ids' => ['nullable', 'array'],
            'job_feature_ids.*' => ['integer', 'exists:m_job_features,id'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['nullable', 'integer', 'in:' . implode(',', $dayIds)],
            'start_working_time' => ['nullable', 'required_with:end_working_time', 'date_format:Hi'],
            'end_working_time' => ['nullable', 'required_with:start_working_time', 'after_or_equal:start_working_time', 'date_format:Hi'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'salary_min.between' => trans('validation.COM.017', ['attribute' => trans('validation.attributes.salary_min')]),
            'salary_min.numeric' => trans('validation.COM.004', ['attribute' => trans('validation.attributes.salary_min')]),
            'salary_max.between' => trans('validation.COM.017', ['attribute' => trans('validation.attributes.salary_max')]),
            'salary_max.numeric' => trans('validation.COM.004', ['attribute' => trans('validation.attributes.salary_max')]),
            'start_working_time.required_with' => trans('validation.COM.022'),
            'end_working_time.required_with' => trans('validation.COM.023'),
            'end_working_time.after_or_equal' => trans('validation.COM.021'),
        ];
    }
}
