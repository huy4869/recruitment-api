<?php

namespace App\Http\Requests\Admin\User;

use App\Models\Gender;
use App\Models\User;
use App\Rules\CheckPhoneNumber;
use App\Rules\FuriUserNameRule;
use Illuminate\Foundation\Http\FormRequest;

class UserInfoUpdateRequest extends FormRequest
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
        if (isset($this->is_public_avatar)) {
            return [
                'is_public_avatar' => 'numeric|in:' . implode(',', [User::STATUS_PUBLIC_AVATAR, User::STATUS_NOT_PUBLIC_AVATAR]),
            ];
        }

        $stringMaxLength = config('validate.string_max_length');
        $zipcodeLength = config('validate.zip_code_max_length');

        return [
            'first_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'last_name' => ['required', 'string', 'max:' . $stringMaxLength],
            'alias_name' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'furi_first_name' => ['required', 'string', 'max:' . $stringMaxLength, new FuriUserNameRule(trans('validation.user_first_name'))],
            'furi_last_name' => ['required', 'string', 'max:' . $stringMaxLength, new FuriUserNameRule(trans('validation.user_last_name'))],
            'birthday' => ['required', 'date', 'before:today'],
            'gender_id' => ['required', 'exists:m_genders,id'],
            'tel' => ['required', 'string', new CheckPhoneNumber()],
            'line' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'facebook' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'instagram' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'twitter' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'postal_code' => ['nullable', 'numeric', 'digits:' . $zipcodeLength],
            'province_id' => ['required', 'numeric', 'exists:m_provinces,id'],
            'province_city_id' => ['required', 'numeric', 'exists:m_provinces_cities,id'],
            'address' => ['required', 'string', 'max:' . $stringMaxLength],
            'building' => ['nullable', 'string', 'max:' . $stringMaxLength],
            'avatar' => ['nullable', 'string', 'url', 'max:' . $stringMaxLength],
            'images' => ['nullable', 'array', 'max:' . config('validate.max_image_detail')],
            'images.*.url' => ['required', 'url', 'string', 'url', 'max:' . $stringMaxLength],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'postal_code.digits' => trans('validation.COM.012', ['attribute' => trans('validation.attributes.postal_code')]),
            'tel.min' => trans('validation.COM.011', ['attribute' => trans('validation.attributes.tel')]),
            'tel.max' => trans('validation.COM.011', ['attribute' => trans('validation.attributes.tel')]),
            'gender_id.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.gender_id')]),
            'birthday.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.birthday')]),
            'province_id.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.province_id')]),
            'province_city_id.required' => trans('validation.COM.010', ['attributes' => trans('validation.attributes.province_city_id')]),
        ];
    }
}
