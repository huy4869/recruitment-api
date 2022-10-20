<?php

namespace App\Http\Requests\User\LearningHistory;

use App\Rules\CheckYearCountRule;
use App\Rules\CheckYearRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class LearningHistoryRequest extends FormRequest
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
        return [
            'school_name' => ['required', 'string', 'max:' . config('validate.string_max_length')],
            'enrollment_period_start' => [
                'required',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before_or_equal:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
                new CheckYearRule(),
            ],
            'enrollment_period_end' => [
                'required',
                'date_format:' . config('date.fe_date_work_history_format'),
                'after_or_equal:enrollment_period_start',
                'before_or_equal:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
                new CheckYearCountRule()
            ],
            'learning_status_id' => ['required', 'integer', 'exists:m_learning_status,id'],
        ];
    }
}
