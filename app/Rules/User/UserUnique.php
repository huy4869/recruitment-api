<?php

namespace App\Rules\User;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UserUnique implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $users = User::query()->where($attribute, $value)->withTrashed()->get();

        foreach ($users as $user) {
            if ($user->role_id == User::ROLE_USER
                || ($user->role_id == User::ROLE_RECRUITER && !isset($user->deleted_at))
                || ($user->role_id == User::ROLE_SUB_ADMIN && !isset($user->deleted_at))
                || ($user->role_id == User::ROLE_ADMIN && !isset($user->deleted_at))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.ERR.002');
    }
}
