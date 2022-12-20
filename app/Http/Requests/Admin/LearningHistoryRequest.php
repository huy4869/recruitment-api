<?php

namespace App\Http\Requests\Admin;

use App\Rules\CheckYearRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class LearningHistoryRequest extends FormRequest
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
        return [
            'school_name' => ['required', 'string', 'max:' . config('validate.string_max_length')],
            'enrollment_period_start' => [
                'required',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before_or_equal:' . Carbon::now()->addYears(config('date.max_year'))->format(config('date.fe_date_work_history_format')),
                new CheckYearRule(),
            ],
            'enrollment_period_end' => [
                'required',
                'date_format:' . config('date.fe_date_work_history_format'),
                'after_or_equal:enrollment_period_start',
                'before_or_equal:' . Carbon::now()->addYears(config('date.max_year'))->format(config('date.fe_date_work_history_format')),
            ],
            'learning_status_id' => ['nullable', 'integer', 'exists:m_learning_status,id'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'enrollment_period_start.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.enrollment_period_start')]),
            'enrollment_period_end.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.enrollment_period_end')]),
            'enrollment_period_start.before_or_equal' => trans('validation.ERR.043'),
            'enrollment_period_end.before_or_equal' => trans('validation.ERR.043'),
            'enrollment_period_end.after_or_equal' => trans('validation.ERR.004'),
        ];
    }
}
