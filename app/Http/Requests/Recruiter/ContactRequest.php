<?php

namespace App\Http\Requests\Recruiter;

use App\Rules\Email;
use App\Rules\PhoneFirstChar;
use App\Rules\PhoneJapan;
use App\Services\Recruiter\StoreService;
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
        $recruiter = auth()->user();

        return [
            'email' => [
                'nullable',
                'string',
                'email',
                new Email(),
                'max:' . config('validate.string_max_length')
            ],
            'store_id' => 'required|integer|exists:stores,id,user_id,' . $recruiter->id,
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
            'content.required' => trans('validation.COM.001'),
        ];
    }
}
