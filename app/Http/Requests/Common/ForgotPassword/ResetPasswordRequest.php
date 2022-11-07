<?php

namespace App\Http\Requests\Common\ForgotPassword;

use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'token' => ['required', 'string', 'max:' . config('validate.string_max_length')],
            'password' => [
                'required',
                'string',
                'confirmed',
                new Password(),
                'min:' . config('validate.password_min_length'),
                'max:' . config('validate.password_max_length')],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'password.min' => trans('validation.COM.005'),
            'password.max' => trans('validation.COM.005'),
            'password.confirmed' => trans('validation.COM.007'),
        ];
    }
}
