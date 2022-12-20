<?php

namespace App\Http\Requests\Admin;

use App\Rules\CheckYearRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class LicensesQualificationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:' . config('validate.string_max_length')],
            'new_issuance_date' => [
                'nullable',
                'date_format:' . config('date.fe_date_work_history_format'),
                'before_or_equal:' . Carbon::now()->format(config('date.fe_date_work_history_format')),
                new CheckYearRule()
            ],
        ];
    }
}
