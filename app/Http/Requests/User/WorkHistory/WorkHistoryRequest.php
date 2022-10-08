<?php

namespace App\Http\Requests\User\WorkHistory;

use App\Models\UserWorkHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class WorkHistoryRequest extends FormRequest
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
        $stringMaxLength = config('validate.string_max_length');
        $type = [UserWorkHistory::TYPE_INACTIVE, UserWorkHistory::TYPE_ACTIVE];

        return [
            'job_types' => ['required', 'array'],
            'job_types.*.name' => ['required', 'string', 'max:' . $stringMaxLength, 'distinct'],
            'work_types' => ['required', 'array'],
            'work_types.*.name' => ['required', 'string', 'max:' . $stringMaxLength, 'distinct'],
            'position_offices' => ['required', 'array'],
            'position_offices.*.name' => ['required', 'string', 'max:' . $stringMaxLength, 'distinct'],
            'store_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'company_name' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'period_check' => ['required', 'integer', 'in:' . implode(',', $type)],
            'period_start' => [
                'required',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before:' . Carbon::now()->format(config('date.fe_date_format')),
            ],
            'period_end' => [
                'nullable',
                'required_if:period_check,=,' . UserWorkHistory::TYPE_INACTIVE,
                'after:period_start',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
            ],
            'business_content' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'experience_accumulation' => ['nullable', 'string'],
        ];
    }
}
