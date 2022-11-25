<?php

namespace App\Http\Requests\Admin\Auth;

use App\Rules\Email;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'password' => [
                'required',
                new Password(),
                'min:' . config('validate.password_min_length'),
                'max:' . config('validate.password_max_length')
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
            'password.min' => trans('validation.COM.005'),
            'password.max' => trans('validation.COM.005'),
        ];
    }
}
