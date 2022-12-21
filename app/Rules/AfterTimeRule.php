<?php

namespace App\Rules;

use App\Models\JobPosting;
use Illuminate\Contracts\Validation\Rule;

class AfterTimeRule implements Rule
{
    protected $rangeHoursType;
    protected $start;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($rangeHoursType, $start)
    {
        $this->rangeHoursType = $rangeHoursType;
        $this->start = $start;
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
        if ($this->rangeHoursType == JobPosting::FULL_DAY && strtotime($value) < strtotime($this->start)) {
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
        return trans('validation.ERR.031');
    }
}
