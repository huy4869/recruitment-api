<?php

namespace App\Http\Requests\User\SearchJob;

use App\Models\MJobType;
use App\Models\MWorkType;
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
        $orderByIds = config('order_by.job_posting_id');

        return [
            'work_type_ids' => 'nullable|array',
            'work_type_ids.*' => 'integer|exists:m_work_types,id,is_default,' . MWorkType::IS_DEFAULT,
            'job_type_ids' => 'nullable|array',
            'job_type_ids.*' => 'integer|exists:m_job_types,id,is_default,' . MJobType::IS_DEFAULT,
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'integer|exists:m_job_features,id',
            'experience_ids' => 'nullable|array',
            'experience_ids.*' => 'integer|exists:m_job_experiences,id',
            'province_id' => 'nullable|array',
            'province_id.*' => 'integer|exists:m_provinces,id',
            'province_city_id' => 'nullable|array',
            'province_city_id.*' => 'integer|exists:m_provinces_cities,id',
            'order_by_ids' => 'nullable|array',
            'order_by_ids.*' => 'integer|in:' . implode(',', $orderByIds),
            'text' => 'nullable|string',
        ];
    }
}
