<?php

namespace App\Http\Requests\Admin;

use App\Rules\Admin\EmailUnique;
use App\Rules\Admin\RoleExist;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'role_id' => ['required', new RoleExist()],
            'alias_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:' . config('validate.email_max_length'), new EmailUnique($this->role_id)],
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
            'email.required' => trans('validation.COM.001'),
            'email.string' => trans('validation.COM.004'),
            'email.email' => trans('validation.COM.002'),
            'email.max' => trans('validation.COM.003'),
            'password.required' => trans('validation.COM.001'),
            'password.min' => trans('validation.COM.005'),
            'password.max' => trans('validation.COM.005'),
            'password_confirmation.required' => trans('validation.COM.001'),
            'password_confirmation.same' => trans('validation.COM.007'),
        ];
    }
}
