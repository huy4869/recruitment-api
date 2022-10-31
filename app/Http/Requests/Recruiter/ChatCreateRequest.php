<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;

class ChatCreateRequest extends FormRequest
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
            'content' => ['required','max:' . config('validate.text_max_length')],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'content.required' => trans('validation.COM.001'),
            'content.max' => trans('validation.COM.014'),
        ];
    }

    /**
     * @return string[]
     */
    public function attributes()
    {
        return [
            'content' => trans('validation.content_chat'),
        ];
    }
}
