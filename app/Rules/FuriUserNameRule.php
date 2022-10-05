<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FuriUserNameRule implements Rule
{
    /**
     * @var
     */
    protected $key;

    /**
     * Create a new rule instance.
     *
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = preg_match('/^[_\x{30A0}-\x{30FF}\s]*$/u', $value);

        return !!$value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->key;
    }
}
