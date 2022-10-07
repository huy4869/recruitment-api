<?php

namespace App\Rules\User;

use App\Models\MFeedbackType;
use Illuminate\Contracts\Validation\Rule;

class FeedbackTypeIds implements Rule
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
        $feedbackTypeIds = MFeedbackType::query()->pluck('id')->toArray();

        return in_array($value, $feedbackTypeIds);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.ERR.012');
    }
}
