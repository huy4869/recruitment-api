<?php

namespace App\Rules\Recruiter;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RecruiterUnique implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $q = User::query()->where($attribute, $value)
            ->whereIn('role_id', [User::ROLE_USER, User::ROLE_RECRUITER, User::ROLE_ADMIN])
            ->withTrashed()->get();

        foreach ($q as $result) {
            if ($result && ($result->role_id == User::ROLE_RECRUITER
                || ($result->role_id == User::ROLE_USER && !isset($result->deleted_at))
                || ($result->role_id == User::ROLE_ADMIN && !isset($result->deleted_at)))) {
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
    public function message(): string
    {
        return trans('validation.ERR.002');
    }
}
