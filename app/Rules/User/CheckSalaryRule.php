<?php

namespace App\Rules\User;

use Illuminate\Contracts\Validation\Rule;

class CheckSalaryRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $salaryMin = request()->salary_min;
        if ($salaryMin && $value && $salaryMin >= $value) {
            return false;
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
        return trans('validation.ERR.044');
    }
}
