<?php

namespace App\Http\Requests\User\Application;

use App\Rules\User\Application\CancelApplied;
use Illuminate\Foundation\Http\FormRequest;

class CancelAppliedRequest extends FormRequest
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
            'application_id' => [
                'required',
                'int',
                new CancelApplied(),
            ]
        ];
    }
}
