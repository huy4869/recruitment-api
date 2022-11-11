<?php

namespace App\Http\Requests\Admin;

use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\UserWorkHistory;
use App\Rules\CheckYearRule;
use App\Services\Admin\WorkHistoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class WorkHistoryUpdateRequest extends FormRequest
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
        $textMaxLength = config('validate.text_max_length_information_pr');
        $type = [UserWorkHistory::TYPE_INACTIVE, UserWorkHistory::TYPE_ACTIVE];

        return [
            'job_types' => ['required', 'array'],
            'job_types.id' => ['nullable', 'exists:m_job_types,id'],
            'job_types.name' => ['required', 'string', 'max:' . $stringMaxLength],
            'work_types' => ['required', 'array'],
            'work_types.id' => ['nullable', 'exists:m_work_types,id'],
            'work_types.name' => ['required', 'string', 'max:' . $stringMaxLength],
            'position_offices' => ['required', 'array'],
            'position_offices.*.id' => ['nullable', 'integer', 'exists:m_position_offices,id'],
            'position_offices.*.name' => ['required', 'string', 'max:' . $stringMaxLength, 'distinct'],
            'store_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'company_name' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'period_check' => ['required', 'integer', 'in:' . implode(',', $type)],
            'period_start' => [
                'required',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before_or_equal:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
                new CheckYearRule()
            ],
            'period_end' => [
                'nullable',
                'required_if:period_check,=,' . UserWorkHistory::TYPE_INACTIVE,
                'date_format:' . config('date.fe_date_work_history_format'),
                'after_or_equal:period_start',
                'before_or_equal:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
            ],
            'business_content' => ['nullable', 'string', 'max:' . $textMaxLength],
            'experience_accumulation' => ['nullable', 'string', 'max:' . $textMaxLength],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'job_types.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.job_types')]),
            'work_types.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.work_types')]),
            'period_start.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.period_start')]),
            'period_end.required_if' => trans('validation.COM.010', ['attribute' => trans('validation.attributes.period_end')]),
            'period_end.after_or_equal' => trans('validation.ERR.004'),
            'user_id.in' => trans('validation.ERR.exist.user_not_exist'),
            'user_id.required' => trans('validation.ERR.exist.user_not_exist')
        ];
    }

    public function attributes()
    {
        return [
            'user_id' => trans('validation.user'),
        ];
    }
}
