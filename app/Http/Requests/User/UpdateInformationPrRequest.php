<?php

namespace App\Http\Requests\User;

use App\Models\User;
use App\Rules\FuriUserNameRule;
use App\Rules\LevelSkillRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInformationPrRequest extends FormRequest
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
            'favorite_skill' => ['nullable', 'string', 'max:' . config('validate.text_max_length_information_pr')],
            'experience_knowledge' => ['nullable', 'string', 'max:' . config('validate.text_max_length_information_pr')],
            'self_pr' => ['nullable', 'string', 'max:' . config('validate.text_max_length_information_pr')],
            'skills' => ['nullable', 'array', new LevelSkillRule()],
        ];
    }
}
