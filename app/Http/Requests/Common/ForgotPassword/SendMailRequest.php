<?php

namespace App\Http\Requests\Common\ForgotPassword;

use App\Rules\Email;
use Illuminate\Foundation\Http\FormRequest;

class SendMailRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                new Email(),
                'max:' . config('validate.email_max_length'),
                'exists:users'
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'email.max' => trans('validation.COM.003'),
            'email.exists' => trans('validation.COM.006'),
        ];
    }
}
