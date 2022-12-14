<?php

namespace App\Http\Requests\Recruiter\Application;

use App\Services\Recruiter\Application\ApplicationService;
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
        $applicationStatuses = ApplicationService::getApplicationStatusIds();

        return [
            'interview_status_id' => 'required|integer|in:' . implode(',', $applicationStatuses),
            'owner_memo' => 'nullable|string|max:' . config('validate.approach_text_max_length'),
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
