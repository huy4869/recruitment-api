<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckYearCountRule implements Rule
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
        $enrollmentPeriodStart = request()->enrollment_period_start;
        $enrollmentPeriodStart = explode('/', $enrollmentPeriodStart);
        $enrollmentPeriodEnd = explode('/', $value);

        if ((int)$enrollmentPeriodEnd[0] - (int)$enrollmentPeriodStart[0] > 5) {
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
        return trans('validation.my_validate.learning_end');
    }
}
