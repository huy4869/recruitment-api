<?php

namespace App\Http\Requests\Recruiter;

use App\Rules\CheckPhoneNumber;
use App\Rules\FuriUserNameRule;
use App\Rules\WithOutFullSize;
use Carbon\Carbon;
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
        $stringMaxLength = config('validate.string_max_length');

        return [
            'first_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'last_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'furi_first_name' => ['required', 'string', 'max:' . $stringMaxLength, new FuriUserNameRule(trans('validation.user_first_name'))],
            'furi_last_name' => ['required', 'string', 'max:' . $stringMaxLength, new FuriUserNameRule(trans('validation.user_last_name'))],
            'company_name' => ['nullable', 'string', 'max:' . $lengthText],
            'home_page_recruiter' => ['nullable', new WithOutFullSize(), 'string', 'max:' . $lengthText],
            'alias_name' => ['nullable', 'string', 'max:' . $lengthText],
            'employee_quantity' => ['nullable', 'string', 'max:' . $lengthText],
            'founded_year' => [
                'nullable',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before_or_equal:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
            ],
            'capital_stock' => ['nullable', 'string', 'max:' . $lengthText],
            'manager_name' => ['nullable', 'string', 'max:' . $lengthText],
            'tel' => ['required', 'string', new CheckPhoneNumber()],
            'postal_code' => ['nullable', 'numeric', 'digits:' . config('validate.zip_code_max_length')],
            'province_id' => ['required', 'numeric', 'exists:m_provinces,id'],
            'province_city_id' => ['required', 'numeric', 'exists:m_provinces_cities,id'],
            'address' => ['required', 'string', 'max:' . config('validate.string_max_length')],
            'building' => ['nullable', 'string', 'max:' . config('validate.string_max_length')],
            'line' => ['nullable', 'string', new WithOutFullSize(), 'max:' . $lengthText],
            'facebook' => ['nullable', 'string', new WithOutFullSize(), 'max:' . $lengthText],
            'instagram' => ['nullable', 'string', new WithOutFullSize(), 'max:' . $lengthText],
            'twitter' => ['nullable', 'string', new WithOutFullSize(), 'max:' . $lengthText],
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
            'founded_year' => trans('validation.store.founded_year'),
        ];
    }
}
