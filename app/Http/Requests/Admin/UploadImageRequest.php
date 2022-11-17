<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
            'image' => ['required', 'mimes:jpg,jpeg,png,svg', 'max:' . config('upload.size_max')],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'image.max' => trans('validation.ERR.003'),
            'image.mimes' => trans('validation.ERR.005'),
        ];
    }
}
