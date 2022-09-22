<?php

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use App\Rules\UserUnique;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $status = [User::STATUS_INACTIVE, User::STATUS_ACTIVE];
        $userId = $this->route('user');
        $nameMaxLength = config('validate.name_max_length');

        return [
            'name' => ['required', 'string', 'max:' . $nameMaxLength],
            'email' => ['required', 'string', 'email', 'max:' . config('validate.email_max_length'), new UserUnique($userId)],
            'status' => ['required', 'in:' . implode(',', $status)],
        ];
    }
}
