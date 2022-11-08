<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\InterviewScheduleService;
use Illuminate\Foundation\Http\FormRequest;

class ListInterviewScheduleRequest extends FormRequest
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
        $recIds = InterviewScheduleService::getRecIds();

        return [
            'start_date' => ['nullable', 'date'],
            'rec_id' => ['required', 'in:' . implode(',', $recIds)],
        ];
    }
}
