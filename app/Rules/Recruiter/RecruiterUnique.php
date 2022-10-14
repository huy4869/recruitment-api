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
        $q = DB::table(User::query()->getQuery()->from)->where($attribute, $value)->whereIn('role_id', [User::ROLE_USER, User::ROLE_RECRUITER]);

        return !$q->count();
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
