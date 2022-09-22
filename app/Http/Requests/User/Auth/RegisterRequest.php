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
            'name' => ['required', 'string', 'max:' . config('validate.name_max_length')],
            'email' => ['required', 'string', 'email', 'max:' . config('validate.email_max_length'), new UserUnique()],
            'password' => ['required', 'confirmed', new Password()],
        ];
    }
}
