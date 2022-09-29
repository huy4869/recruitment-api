<?php

namespace App\Http\Requests\User\Auth;

use App\Rules\Password;
use App\Rules\UserUnique;
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
            'email' => ['required', 'string', 'email', 'max:' . config('validate.email_max_length'), new UserUnique()],
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
            'email.required' => trans('validation.COM_001'),
            'email.string' => trans('validation.COM_004'),
            'email.email' => trans('validation.COM_002'),
            'email.max' => trans('validation.COM_003'),
            'password.required' => trans('validation.COM_001'),
            'password.min' => trans('validation.COM_005'),
            'password.max' => trans('validation.COM_005'),
            'password_confirmation.required' => trans('validation.COM_001'),
            'password_confirmation.same' => trans('validation.COM_007'),
        ];
    }

}
