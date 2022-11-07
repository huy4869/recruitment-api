<?php

namespace App\Http\Requests\Admin\Auth;

use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'required',
            'password' => [
                'required',
                'confirmed',
                new Password(),
                'min:' . config('validate.password_min_length'),
                'max:' . config('validate.password_max_length'),
            ],
            'password_confirmation' => 'required',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'password_confirmation.required' => trans('validation.COM.001'),
            'password.min' => trans('validation.COM.005'),
            'password.max' => trans('validation.COM.005'),
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'current_password' => '現在のパスワード',
            'password' => '新しいパスワード',
            'password_confirmation' => '新しいパスワード確認',
        ];
    }
}
