<?php

namespace App\Http\Requests\Recruiter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        return [
            'start_date' => ['nullable', 'date'],
            'store_ids' => ['array', 'nullable'],
            'store_ids.*' => ['numeric', Rule::exists('stores', 'id')
                ->where('deleted_at')->where('user_id', Auth::user()->id)],
        ];
    }
}
