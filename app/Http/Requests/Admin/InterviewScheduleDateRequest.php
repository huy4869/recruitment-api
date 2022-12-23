<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InterviewScheduleDateRequest extends FormRequest
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
            'store_id' => [
                'required',
                'numeric',
                Rule::exists('stores', 'id')
                    ->where('deleted_at'),
            ],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'date.after_or_equal' => trans('validation.ERR.038'),
        ];
    }
}
