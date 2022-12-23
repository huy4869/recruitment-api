<?php

namespace App\Http\Requests\Recruiter\User;

use App\Models\FavoriteUser;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FavoriteRequest extends FormRequest
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
        $actionTypes = [
            FavoriteUser::UNFAVORITE_USER,
            FavoriteUser::FAVORITE_USER,
        ];

        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('deleted_at')->where('role_id', User::ROLE_USER),
            ],
            'action_type' => 'required|integer|in:' . implode(',', $actionTypes),
        ];
    }
}
