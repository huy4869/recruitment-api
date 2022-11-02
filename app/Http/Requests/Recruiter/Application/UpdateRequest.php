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
            'approach' => 'nullable|string|max:' . config('validate.approach_text_max_length'),
        ];
    }
}
