<?php

namespace App\Http\Requests\Admin;

use App\Rules\Admin\CurrentPassword;
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
            'current_password' => ['required', new Password(), new CurrentPassword()],
            'password' => ['required', new Password(), 'min:' . config('validate.password_min_length'), 'max:' . config('validate.password_max_length')],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    /**
     * Get the validation messages
     * @return array
     */
    public function messages()
    {
        return [
            'current_password.required' => trans('validation.COM.001'),
            'password.required' => trans('validation.COM.001'),
            'password.min' => trans('validation.COM.005'),
            'password.max' => trans('validation.COM.005'),
            'password_confirmation.required' => trans('validation.COM.001'),
            'password_confirmation.same' => trans('validation.COM.007'),
        ];
    }

    /**
     * attributes
     *
     * @return string[]
     */
    public function attributes()
    {
        return [
            'password' => '新しいパスワード',
            'password_confirmation' => '新しいパスワード確認',
        ];
    }
}
