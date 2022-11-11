<?php

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use App\Rules\Admin\EmailUnique;
use App\Rules\Email;
use App\Rules\FuriUserNameRule;
use App\Rules\Password;
use App\Rules\User\UserUnique;
use App\Services\Admin\User\UserService;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        $userRoleIds = UserService::getUserRoleIdCanModify(auth()->user()->role_id);

        return [
            'role_id' => 'required|integer|in:' . implode(',', $userRoleIds),
            'first_name' => 'nullable|string|max:' . config('validate.string_max_length'),
            'last_name' => 'nullable|string|max:' . config('validate.string_max_length'),
            'furi_first_name' => [
                'nullable',
                'string',
                new FuriUserNameRule(trans('validation.user_first_name')),
                'max:' . config('validate.string_max_length')
            ],
            'furi_last_name' => [
                'nullable',
                'string',
                new FuriUserNameRule(trans('validation.user_last_name')),
                'max:' . config('validate.string_max_length')
            ],
            'email' => [
                'required',
                'string',
                'email',
                new Email(),
                'max:' . config('validate.email_max_length'),
                new EmailUnique($this->role_id),
            ],
            'password' => [
                'required',
                'confirmed',
                new Password(),
                'min:' . config('validate.password_min_length'),
                'max:' . config('validate.password_max_length')
            ],
            'password_confirmation' => [
                'required',
                'same:password'
            ],
            //handle jobs and applications when adding stores
            'store_ids' => 'nullable|array',
            'store_ids.*' => 'integer|exists:stores,id,user_id,NULL'
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'email.max' => trans('validation.COM.003'),
            'first_name.max' => trans('validation.COM.008'),
            'last_name.max' => trans('validation.COM.008'),
            'furi_first_name.max' => trans('validation.COM.009'),
            'furi_last_name.max' => trans('validation.COM.009'),
            'password_confirmation.same' => trans('validation.COM.007'),
        ];
    }
}