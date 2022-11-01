<?php

namespace App\Http\Requests\User;

use App\Models\MFeedbackType;
use App\Rules\User\FeedbackTypeIds;
use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
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
            'feedback_type_ids' => 'required|array',
            'feedback_type_ids.*' => [
                'integer',
                new FeedbackTypeIds(),
            ],
            'content' => 'nullable|string|max:' . config('validate.text_max_length'),
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'content.max' => trans('validation.custom.feedback.content_max'),
        ];
    }
}
