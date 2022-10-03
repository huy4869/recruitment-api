<?php

namespace App\Rules\Admin;

use App\Exceptions\InputException;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class EmailUnique implements Rule
{
    /**
     * @var int $role_id
     */
    public $role_id;

    /**
     * Create a new rule instance
     *
     * @param $role_id
     */
    public function __construct($role_id)
    {
        $this->role_id = $role_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        switch ($this->role_id) {
            case User::ROLE_USER:
                $whereRole = [User::ROLE_USER, User::ROLE_SUB_ADMIN, User::ROLE_ADMIN, User::ROLE_RECRUITER];
                break;
            case User::ROLE_RECRUITER:
                $whereRole = [User::ROLE_USER, User::ROLE_RECRUITER];
                break;
            case User::ROLE_SUB_ADMIN:
                $whereRole = [User::ROLE_USER, User::ROLE_SUB_ADMIN, User::ROLE_ADMIN];
                break;
            default:
                return true;
        }

        $users = User::where($attribute, $value)->whereIn('role_id', $whereRole)->get();

        return !$users->count();
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
