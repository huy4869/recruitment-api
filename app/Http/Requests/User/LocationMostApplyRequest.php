<?php

namespace App\Http\Requests\User;

use App\Rules\Email;
use App\Rules\PhoneFirstChar;
use App\Rules\PhoneJapan;
use Illuminate\Foundation\Http\FormRequest;

class LocationMostApplyRequest extends FormRequest
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
            'types' => 'nullable|array',
            'limit' => 'nullable|number',
        ];
    }
}
