<?php

namespace App\Http\Requests\Recruiter;

use App\Services\Recruiter\InterviewScheduleService;
use Illuminate\Foundation\Http\FormRequest;

class InterviewScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $isHasInterview = [InterviewScheduleService::NO_HAS_INTERVIEW, InterviewScheduleService::IS_HAS_INTERVIEW];

        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'hours' => ['required', 'string', 'in:' . implode(',', config('date.time'))],
            'is_has_interview' => ['required', 'in:' . implode(',', $isHasInterview)],
        ];
    }
}