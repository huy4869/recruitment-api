<?php

namespace App\Rules\User\Application;

use App\Models\Application;
use App\Models\MInterviewStatus;
use Illuminate\Contracts\Validation\Rule;

class CancelApplied implements Rule
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
        $application = Application::query()->where('id', $value)->first();
        $user = auth()->user();

        if ($application->user_id != $user->id) {
            return false;
        }

        if ($application->interview_status_id != MInterviewStatus::STATUS_APPLYING
        && $application->interview_status_id != MInterviewStatus::STATUS_WAITING_INTERVIEW
        && $application->interview_status_id != MInterviewStatus::STATUS_WAITING_RESULT
        ) {
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
        return trans('response.invalid');
    }
}
