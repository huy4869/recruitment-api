<?php

namespace App\Http\Requests\Admin;

use App\Rules\LevelSkillRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrRequest extends FormRequest
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
        $textMaxLength = config('validate.text_max_length_information_pr');

        return [
            'favorite_skill' => ['nullable', 'string', 'max:' . $textMaxLength],
            'experience_knowledge' => ['nullable', 'string', 'max:' . $textMaxLength],
            'self_pr' => ['nullable', 'string', 'max:' . $textMaxLength],
            'skills' => ['nullable', 'array', new LevelSkillRule()],
        ];
    }
}
