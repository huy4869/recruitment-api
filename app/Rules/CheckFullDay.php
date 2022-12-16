<?php

namespace App\Rules;

use App\Models\JobPosting;
use Illuminate\Contracts\Validation\Rule;

class CheckFullDay implements Rule
{
    protected $rangeHoursType;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($rangeHoursType)
    {
        $this->rangeHoursType = $rangeHoursType;
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
        if ($this->rangeHoursType == JobPosting::FULL_DAY) {
            return !!preg_match('/^(1[0-9]|0?[0-9]|2[0-3]):00|30/', $value);
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
        return trans('validation.COM.998');
    }
}
