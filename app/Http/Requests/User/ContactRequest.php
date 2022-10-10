<?php

namespace App\Http\Requests\User;

use App\Rules\Email;
use App\Rules\PhoneFirstChar;
use App\Rules\PhoneJapan;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                new Email(),
                'max:' . config('validate.string_max_length')
            ],
            'name' => 'nullable|string|max:' . config('validate.string_max_length'),
            'tel' => [
                'nullable',
                new PhoneFirstChar(),
                new PhoneJapan(),
                'min:' . config('validate.phone_min_length'),
                'max:' . config('validate.phone_max_length'),
            ],
            'content' => 'required|string|max:' . config('validate.text_max_length'),
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'tel.min' => trans('validation.COM.011'),
            'tel.max' => trans('validation.COM.011'),
            'content.max' => trans('validation.COM.014'),
        ];
    }
}