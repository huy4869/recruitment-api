<?php

namespace App\Http\Requests\User\SearchJob;

use App\Helpers\JobHelper;
use App\Services\Common\CommonService;
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
        $masterData = JobHelper::getJobMasterData();
        $masterDataIds = JobHelper::getListIdsMasterData($masterData);
        $masterDataLocation = CommonService::getListIdsLocationMasterData();
        $orderByIds = config('order_by.job_posting_id');

        return [
            'work_type_ids' => 'nullable|array',
            'work_type_ids.*' => 'integer|in:' . implode(',', $masterDataIds['masterWorkTypes']),
            'job_type_ids' => 'nullable|array',
            'job_type_ids.*' => 'integer|in:' . implode(',', $masterDataIds['masterJobTypes']),
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'integer|in:' . implode(',', $masterDataIds['masterJobFeatures']),
            'experience_ids' => 'nullable|array',
            'experience_ids.*' => 'integer|in:' . implode(',', $masterDataIds['masterJobExperiences']),
            'province_id' => 'nullable|array',
            'province_id.*' => 'integer|in:' . implode(',', $masterDataLocation['provinceIds']),
            'province_city_id' => 'nullable|array',
            'province_city_id.*' => 'integer|in:' . implode(',', $masterDataLocation['provinceCityIds']),
            'order_by_ids' => 'nullable|array',
            'order_by_ids.*' => 'integer|in:' . implode(',', $orderByIds),
            'text' => 'nullable|string',
        ];
    }
}
