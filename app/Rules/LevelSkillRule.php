<?php

namespace App\Rules;

use App\Exceptions\InputException;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class LevelSkillRule implements Rule
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
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $skillAmount = count(config('user.skill_types'));
        $uniqueValue = collect($value)->unique('type')->toArray();

        if (count($value) < $skillAmount || count($uniqueValue) != count($value)) {
            return false;
        }

        foreach ($value as $skill) {
            $skillLevelArray = config('user.skill_types.' . $skill['type']);

            if (
                !isset($skill['type'])
                || !isset($skill['level'])
                || !isset($skillLevelArray['level'])
                || !isset($skillLevelArray['level'][$skill['level']])
            ) {
                return false;
            }
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
        return trans('response.invalid');
    }
}
