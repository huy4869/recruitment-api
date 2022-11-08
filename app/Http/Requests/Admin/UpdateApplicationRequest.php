<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationRequest extends FormRequest
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
            'user_id' => ['required', 'numeric'],
            'date' => ['required', 'date'],
            'hours' => ['required', 'string'],
            'interview_approaches_id' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:' . config('validate.text_max_length')],
        ];
    }
}
