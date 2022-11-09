<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckPhoneNumber implements Rule
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
        $countValue = strlen($value);
        $valueNumber = str_replace('-', '', $value);
        $countValueNumber = strlen($valueNumber);

        if ($countValue < config('validate.phone_min_length')
            || $countValue > config('validate.phone_max_length')
            || $countValueNumber < config('validate.string_phone_min_length')
            || $countValueNumber > config('validate.string_phone_max_length')
            || !preg_match('/^0[0-9]+$/', $valueNumber)) {
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
        return trans('validation.COM.011');
    }
}
