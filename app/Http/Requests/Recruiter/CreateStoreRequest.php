<?php

namespace App\Http\Requests\Recruiter;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
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
        $lengthText = config('validate.string_max_length');

        return [
            'url' => ['nullable', 'string', 'url'],
            'name' => ['required', 'string', 'max:' . $lengthText],
            'website' => ['nullable', 'max:' . $lengthText],
            'tel' => [
                'required',
                'numeric',
                'regex:/(0)[0-9]/',
                'digits_between:' . config('validate.phone_min_length') . ',' . config('validate.phone_max_length')
            ],
            'postal_code' => ['nullable', 'numeric', 'digits:' . config('validate.zip_code_max_length')],
            'province_id' => ['required', 'numeric', 'exists:m_provinces,id'],
            'province_city_id' => ['required', 'numeric', 'exists:m_provinces,id'],
            'city' => ['required', 'string'],
            'address' => ['nullable', 'string', 'max:' . $lengthText],
            'manager_name' => ['nullable', 'string', 'max:' . $lengthText],
            'recruiter_name' => ['nullable', 'string', 'max:' . $lengthText],
            'employee_quantity' => ['nullable', 'string', 'max:' . $lengthText],
            'founded_year' => [
                'nullable',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before_or_equal:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
            ],
            'business_segment' => ['nullable', 'string', 'max:' . config('validate.text_max_length_information_pr')],
            'specialize_ids' => ['nullable', 'array', 'exists:m_job_types,id'],
            'store_features' => ['nullable', 'string', 'max:' . config('validate.text_max_length_information_pr')],
        ];
    }

    public function messages()
    {
        return [
            'max' => trans('validation.COM.008'),
            'province_id.required' => trans('validation.COM.010'),
            'province_city_id.required' => trans('validation.COM.010'),
            'business_segment.max' => trans('validation.COM.013'),
            'store_features.max' => trans('validation.COM.013'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => trans('validation.store.name'),
            'website' => trans('validation.store.website'),
            'province_city_id' => trans('validation.store.province_city_id'),
            'city' => trans('validation.store.city'),
            'manager_name' => trans('validation.store.manager_name'),
            'founded_year' => trans('validation.store.founded_year'),
        ];
    }
}
