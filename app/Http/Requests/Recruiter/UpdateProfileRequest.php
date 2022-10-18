<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $lengthText = config('validate.max_length_text');

        return [
            'company_name' => ['nullable', 'string', 'max:' . $lengthText],
            'home_page_rescuiter' => ['nullable', 'string', 'max:' . $lengthText],
            'alias_name' => ['nullable', 'string', 'max:' . $lengthText],
            'employee_quantity' => ['nullable', 'string', 'max:' . $lengthText],
            'year' => ['nullable', 'digits:' . config('validate.year')],
            'month' => ['nullable', 'digits_between:' . config('validate.month.min_length') . ',' . config('validate.month.max_length')],
            'capital_stock' => ['nullable', 'numeric', 'min:1'],
            'manager_name' => ['nullable', 'string', 'max:' . $lengthText],
            'tel' => [
                'required',
                'numeric',
                'regex:/(0)[0-9]/',
                'digits_between:' . config('validate.phone_min_length') . ',' . config('validate.phone_max_length')
            ],
            'postal_code' => ['nullable', 'numeric', 'digits:' . config('validate.zip_code_max_length')],
            'province_id' => ['required', 'numeric', 'exists:m_provinces,id'],
            'city' => ['required', 'string', 'max:' . config('validate.string_max_length')],
            'address' => ['nullable', 'string', 'max:' . config('validate.string_max_length')],
            'line' => ['nullable', 'string', 'max:' . $lengthText],
            'facebook' => ['nullable', 'string', 'max:' . $lengthText],
            'instagram' => ['nullable', 'string', 'max:' . $lengthText],
            'twitter' => ['nullable', 'string', 'max:' . $lengthText],
        ];
    }

    public function messages()
    {
        return [
            'max' => trans('validation.COM.008'),
            'min' => trans('validation.is_positive_number'),
            'tel.digits_between' => trans('validation.COM.011'),
            'province_id.required' => trans('validation.COM.010'),
            'line.max' => trans('validation.COM.003'),
            'facebook.max' => trans('validation.COM.003'),
            'instagram.max' => trans('validation.COM.003'),
            'twitter.max' => trans('validation.COM.003'),
        ];
    }

    public function attributes()
    {
        return [
            'company_name' => trans('common.company_name'),
            'line' => trans('common.line'),
            'facebook' => trans('common.facebook'),
            'instagram' => trans('common.instagram'),
            'twitter' => trans('common.twitter'),
        ];
    }
}
