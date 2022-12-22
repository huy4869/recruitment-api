<?php

namespace App\Rules;

use App\Models\JobPosting;
use Illuminate\Contracts\Validation\Rule;

class AfterTimeHalfDayRule implements Rule
{
    protected $timeType;
    protected $startTimeType;
    protected $endTimeType;
    protected $startTime;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($timeType, $startTimeType, $endTimeType, $startTime)
    {
        $this->timeType = $timeType;
        $this->startTimeType = $startTimeType;
        $this->endTimeType = $endTimeType;
        $this->startTime = $startTime;
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
        if ($this->timeType == JobPosting::HALF_DAY) {
            if ($this->startTimeType == $this->endTimeType && strtotime($value) < strtotime($this->startTime)) {
                return false;
            }
            if ($this->startTimeType == JobPosting::TYPE_AFTERNOON && $this->endTimeType == JobPosting::TYPE_MORNING) {
                return false;
            }

            return true;
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
