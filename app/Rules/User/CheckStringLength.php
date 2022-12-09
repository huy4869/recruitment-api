<?php

namespace App\Rules\User;

use Illuminate\Contracts\Validation\Rule;

class CheckStringLength implements Rule
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
        $str = array_merge_recursive(...$value)['name'];
        if (is_string($str)) {
            $strLen = strlen($str);
        } else {
            $strLen = strlen(implode('', $str));
        }

        if ($strLen > config('validate.string_max_length')) {
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
        return __('validation.COM.008', ['attribute' => __('validation.attributes.position_offices')]);
    }
}
