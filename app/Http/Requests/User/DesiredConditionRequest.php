<?php

namespace App\Http\Requests\User;

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
        $ageType = array_keys(config('date.age'));

        return [
            'province_id' => ['required', 'numeric', 'exists:m_provinces,id'],
            'work_type_ids' => ['required', 'array'],
            'work_type_ids.*.id' => ['required', 'numeric', 'exists:m_work_types,id'],
            'age_id' => ['required', 'numeric', 'in:' . implode(',', $ageType)],
            'salary_type_id' => ['required', 'numeric', 'exists:m_salary_types,id'],
            'salary_min' => ['required', 'numeric', 'min:1'],
            'salary_max' => ['required', 'numeric', 'gt:salary_min'],
            'job_type_ids' => ['required', 'array'],
            'job_type_ids.*.id' => ['required', 'integer', 'exists:m_job_types,id'],
            'job_experience_ids' => ['required', 'array'],
            'job_experience_ids.*.id' => ['required', 'integer', 'exists:m_job_experiences,id'],
            'job_feature_ids' => ['required', 'array'],
            'job_feature_ids.*.id' => ['required', 'integer', 'exists:m_job_features,id'],
        ];
    }
}
