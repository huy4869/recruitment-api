<?php

namespace App\Http\Requests\Admin\Application;

use App\Services\Admin\Application\ApplicationService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'interview_status_id' => 'required|integer|exists:m_interviews_status,id',
            'owner_memo' => 'nullable|string|max:' . config('validate.approach_text_max_length'),
            'date' => ['nullable', 'date'],
            'hours' => ['nullable', 'string', 'in:' . implode(',', config('date.time'))],
            'interview_approach_id' => ['nullable', 'numeric', 'exists:m_interview_approaches,id'],
            'note' => ['nullable', 'string', 'max:' . config('validate.text_max_length')],
            'meet_link' => ['nullable', 'string', 'max:' . config('validate.max_length_text')],
        ];
    }

    public function messages()
    {
        return [
            'meet_link.max' => trans('validate.COM.003'),
        ];
    }
}
