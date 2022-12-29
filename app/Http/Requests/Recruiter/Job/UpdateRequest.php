<?php

namespace App\Http\Requests\Recruiter\Job;

use App\Models\JobPosting;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Rules\AfterTimeHalfDayRule;
use App\Rules\AfterTimeRule;
use App\Rules\CheckFullDay;
use App\Rules\CheckHoursRule;
use App\Rules\Recruiter\ExistStoreByRec;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $dayIds = array_keys(config('date.day_of_week_ja_fe'));
        $recruiter = auth()->user();
        $rangeHoursType = [JobPosting::FULL_DAY, JobPosting::HALF_DAY];
        $requireOrNullable = $this->job_status_id == JobPosting::STATUS_DRAFT ? 'nullable' : 'required';
        $timeTypes = [JobPosting::TYPE_MORNING, JobPosting::TYPE_AFTERNOON];
        $timeTypesCheck = $this->range_hours_type == JobPosting::HALF_DAY ? 'required' : 'nullable';

        return [
            'name' => $requireOrNullable . '|string|max:' . config('validate.string_max_length'),
            'store_id' => [
                $requireOrNullable,
                'integer',
                Rule::exists('stores', 'id')
                    ->where('deleted_at')->where('user_id', $recruiter->id),
                ],
            'job_status_id' => $requireOrNullable . '|integer|exists:m_job_statuses,id',
            'pick_up_point' => 'nullable|string|max:' . config('validate.job_posting_textarea_max_length'),
            'job_banner' => $requireOrNullable . '|string|url',
            'job_thumbnails' => $requireOrNullable . '|array',
            'job_thumbnails.*' => 'string|url',
            'job_type_ids' => $requireOrNullable . '|array',
            'job_type_ids.*' => 'integer|exists:m_job_types,id,is_default,' . MJobType::IS_DEFAULT,
            'description' => $requireOrNullable . '|string|max:' . config('validate.job_posting_textarea_max_length'),
            'work_type_ids' => $requireOrNullable . '|array',
            'work_type_ids.*' => 'integer|exists:m_work_types,id,is_default,' . MWorkType::IS_DEFAULT,
            'salary_type_id' => $requireOrNullable . '|integer|exists:m_salary_types,id',
            'salary_min' => $requireOrNullable . '|integer|max:' . config('validate.salary_max_value'),
            'salary_max' => $requireOrNullable . '|integer|greater_than_field:salary_min|max:' . config('validate.salary_max_value'),
            'salary_description' => 'nullable|string|max:' . config('validate.job_posting_textarea_max_length'),
            'range_hours_type' => 'integer|in:' . implode(',', $rangeHoursType),
            'start_work_time_type' => $timeTypesCheck . '|integer|in:' . implode(',', $timeTypes),
            'end_work_time_type' => $timeTypesCheck . '|integer|in:' . implode(',', $timeTypes),
            'start_work_time' => [
                $requireOrNullable,
                'string',
                'date_format:H:i',
                'max:' . config('validate.work_time_max_length'),
                new CheckFullDay($this->range_hours_type),
                new CheckHoursRule($this->range_hours_type, $this->start_work_time_type)
            ],
            'end_work_time' => [
                $requireOrNullable,
                'string',
                'date_format:H:i',
                'max:' . config('validate.work_time_max_length'),
                new AfterTimeRule($this->range_hours_type, $this->start_work_time),
                new AfterTimeHalfDayRule($this->range_hours_type, $this->start_work_time_type, $this->end_work_time_type, $this->start_work_time),
                new CheckFullDay($this->range_hours_type),
                new CheckHoursRule($this->range_hours_type, $this->end_work_time_type),
            ],
            'shifts' => 'nullable|max:' . config('validate.job_posting_textarea_max_length'),
            'age_min' => 'nullable|integer|min:' . config('validate.age.min') . '|max:' . config('validate.age.max'),
            'age_max' => 'nullable|integer|greater_than_field:age_min|max:' . config('validate.age.max'),
            'gender_ids' => 'nullable|array',
            'gender_ids.*' => 'integer|exists:m_genders,id',
            'experience_ids' => 'nullable|array',
            'experience_ids.*' => 'integer|exists:m_job_experiences,id',
            'postal_code' => ['nullable', 'numeric', 'digits:' . config('validate.zip_code_max_length')],
            'province_id' => $requireOrNullable . '|numeric|exists:m_provinces,id',
            'province_city_id' => $requireOrNullable . '|numeric|exists:m_provinces_cities,id,province_id,' . $this->province_id,
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['nullable', 'integer', 'in:' . implode(',', $dayIds)],
            'address' => $requireOrNullable . '|max:' . config('validate.string_max_length'),
            'building' => 'nullable|max:' . config('validate.string_max_length'),
            'station_ids' => 'nullable|array',
            'stations_ids.*' => 'integer|exists:m_stations,id',
            'welfare_treatment_description' => $requireOrNullable . '|max:' . config('validate.job_posting_textarea_max_length'),
            'feature_ids' => 'nullable|array',
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
            'description.max' => trans('validation.COM.013', [
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
            'salary_description.max' => trans('validation.COM.013', [
                'attribute' => trans('job_posting.attributes.salary_description')
            ]),
            'shifts.max' => trans('validation.COM.013', [
                'attribute' => trans('job_posting.attributes.shifts')
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
            'welfare_treatment_description.max' => trans('validation.COM.013', [
                'attribute' => trans('job_posting.attributes.welfare_treatment_description')
            ]),
        ];
    }
}
