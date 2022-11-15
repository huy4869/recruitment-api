<?php

namespace App\Http\Requests\User\Application;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'id' => ['required', 'numeric', 'exists:job_postings,id'],
            'date' => ['required', 'date'],
            'hours' => ['required', 'string'],
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
            'hours.required' => trans('validation.COM.010', ['attribute' => trans('validation.attributes.hours')]),
        ];
    }
}
