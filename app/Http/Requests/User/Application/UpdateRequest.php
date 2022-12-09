<?php

namespace App\Http\Requests\User\Application;

use Carbon\Carbon;
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
        return [
            'date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:' . Carbon::now()->addDays(config('date.max_day'))],
            'hours' => ['required', 'string', 'in:' . implode(',', config('date.time'))],
            'interview_approaches_id' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:' . config('validate.text_max_length')],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'note.max' => trans('validation.COM.014'),
            'interview_approaches_id.required' => trans('validation.COM.010', ['attribute' => trans('validation.attributes.interview_approaches_id')]),
            'date.required' => trans('validation.COM.010', ['attribute' => trans('validation.attributes.date')]),
            'date.before_or_equal' => __('validation.ERR.037'),
            'hours.required' => trans('validation.COM.010', ['attribute' => trans('validation.attributes.hours')]),
            'hours.in' => __('validation.ERR.037'),
        ];
    }
}
