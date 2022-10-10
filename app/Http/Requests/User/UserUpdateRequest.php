<?php

namespace App\Http\Requests\User;

use App\Models\User;
use App\Rules\FuriUserNameRule;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $gender = [User::GENDER_FEMALE, User::GENDER_MALE, User::GENDER_THIRD];
        $phoneMaxLength = config('validate.phone_max_length');
        $phoneMinLength = config('validate.phone_min_length');
        $stringMaxLength = config('validate.string_max_length');
        $zipcodeLength = config('validate.zip_code_max_length');

        return [
            'first_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'last_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'alias_name' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'furi_first_name' => ['required', 'string', 'max:' . $stringMaxLength, new FuriUserNameRule(trans('validation.user_first_name'))],
            'furi_last_name' => ['required', 'string', 'max:' . $stringMaxLength, new FuriUserNameRule(trans('validation.user_last_name'))],
            'birthday' => ['required', 'date', 'before:today'],
            'age' => ['nullable', 'numeric'],
            'gender_id' => ['required', 'in:' . implode(',', $gender)],
            'tel' => ['required', 'numeric', 'regex:/(0)[0-9]/', 'digits_between:' . $phoneMinLength . ',' . $phoneMaxLength],
            'email' => ['required', 'email', 'string', 'max:' . config('validate.email_max_length')],
            'line' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'facebook' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'instagram' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'twitter' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'postal_code' => ['nullable', 'numeric', 'digits:' . $zipcodeLength],
            'province_id' => ['required', 'numeric', 'exists:m_provinces,id'],
            'city' => ['required', 'string', 'max:' . $stringMaxLength],
            'address' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'avatar' => ['nullable', 'string', 'url', 'max:' . $stringMaxLength],
            'images' => ['nullable', 'array', 'max:' . config('validate.max_image_detail')],
            'images.*.url' => ['required', 'url', 'string', 'url', 'max:' . $stringMaxLength],
        ];
    }
}