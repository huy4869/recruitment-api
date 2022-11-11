<?php

namespace App\Http\Requests\Recruiter\Job;

use App\Models\MJobType;
use App\Models\MWorkType;
use App\Services\Recruiter\Job\JobService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $recruiter = auth()->user();
        $storeIds = JobService::getStoreIdsAccordingToRecruiter($recruiter);

        return [
            'name' => 'required|string|max:' . config('validate.string_max_length'),
            'store_id' => 'required|integer|in:' . implode(',', $storeIds),
            'job_status_id' => 'required|integer|exists:m_job_statuses,id',
            'pick_up_point' => 'nullable|string|max:' . config('validate.text_max_length'),
            'job_banner' => 'required|string',
            'job_thumbnails' => 'required|array',
            'job_thumbnails.*' => 'string',
            'job_type_ids' => 'required|array',
            'job_type_ids.*' => 'integer|exists:m_job_types,id,is_default,' . MJobType::IS_DEFAULT,
            'description' => 'required|string|max:' . config('validate.text_max_length'),
            'work_type_ids' => 'required|array',
            'work_type_ids.*' => 'integer|exists:m_work_types,id,is_default,' . MWorkType::IS_DEFAULT,
            'salary_type_id' => 'required|integer|exists:m_salary_types,id',
            'salary_min' => 'required|integer|max:' . config('validate.salary_max_value'),
            'salary_max' => 'required|integer|greater_than_field:salary_min|max:' . config('validate.salary_max_value'),
            'salary_description' => 'nullable|string|max:' . config('validate.string_max_length'),
            'start_work_time' => 'string|max:' . config('validate.work_time_max_length'),
            'end_work_time' => 'string|greater_than_field:start_work_time|max:' . config('validate.work_time_max_length'),
            'shifts' => 'nullable|max:' . config('validate.text_max_length'),
            'age_min' => 'nullable|integer|min:' . config('validate.age.min') . '|max:' . config('validate.age.max'),
            'age_max' => 'nullable|integer|greater_than_field:age_min|max:' . config('validate.age.max'),
            'gender_ids' => 'nullable|array',
            'gender_ids.*' => 'integer|exists:m_genders,id',
            'experience_ids' => 'nullable|array',
            'experience_ids.*' => 'integer|exists:m_job_experiences,id',
            'postal_code' => 'required',
            'province_id' => 'required',
            'city' => 'required|max:' . config('validate.string_max_length'),
            'address' => 'nullable|max:' . config('validate.string_max_length'),
            'station_ids' => 'nullable|array',
            'stations_ids.*' => 'integer|exists:m_stations,id',
            'welfare_treatment_description' => 'required|max:' . config('validate.text_max_length'),
            'feature_ids' => 'required|array',
            'feature_ids.*' => 'integer|exists:m_job_features,id',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'store_id.required' => trans('validation.COM.010', [
                'attribute' => trans('job_posting.attributes.store_id')
            ]),
            'pick_up_point.required' => trans('validation.COM.014', [
                'attribute' => trans('job_posting.attributes.pick_up_point')
            ]),
            'job_banner.required' => trans('validation.COM.020', [
                'attribute' => trans('job_posting.attributes.job_banner')
            ]),
            'job_details.required' => trans('validation.COM.020', [
                'attribute' => trans('job_posting.attributes.job_details')
            ]),
            'job_type_ids.required' => trans('validation.COM.010', [
                'attribute' => trans('job_posting.attributes.job_type_ids')
            ]),
            'feature_ids.required' => trans('validation.COM.010', [
                'attribute' => trans('job_posting.attributes.feature_ids')
            ]),
            'description.max' => trans('validation.COM.014', [
                'attribute' => trans('job_posting.attributes.description')
            ]),
            'salary_min.max' => trans('validation.COM.017', [
                'attribute' => trans('job_posting.attributes.salary_min')
            ]),
            'salary_min.integer' => trans('validation.COM.019', [
                'attribute' => trans('job_posting.attributes.salary_min')
            ]),
            'salary_max.max' => trans('validation.COM.017', [
                'attribute' => trans('job_posting.attributes.salary_max')
            ]),
            'salary_max.integer' => trans('validation.COM.019', [
                'attribute' => trans('job_posting.attributes.salary_max')
            ]),
            'salary_max.greater_than_field' => trans('validation.ERR.028'),
            'salary_type_id.required' => trans('validation.ERR.030'),
            'salary_description.max' => trans('validation.COM.014', [
                'attribute' => trans('job_posting.attributes.salary_description')
            ]),
            'start_work_time.required' => trans('validation.COM.010', [
                'attribute' => trans('job_posting.attributes.start_work_time')
            ]),
            'end_work_time.required' => trans('validation.COM.010', [
                'attribute' => trans('job_posting.attributes.end_work_time')
            ]),
            'end_work_time.greater_than_field' => trans('validation.ERR.031'),
            'age_min.min' => trans('validation.ERR.040'),
            'age_min.max' => trans('validation.ERR.033'),
            'age_max.max' => trans('validation.ERR.033'),
            'age_max.greater_than_field' => trans('validation.ERR.032'),
            'province_id.required' => trans('validation.COM.010', [
                'attribute' => trans('job_posting.attributes.province_id')
            ]),
            'welfare_treatment_description.max' => trans('validation.COM.014', [
                'attribute' => trans('job_posting.attributes.welfare_treatment_description')
            ]),
        ];
    }
}
