<?php

namespace App\Http\Resources\Admin\User;

use App\Http\Resources\User\WorkHistory\NameTypeResource;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Services\Admin\WorkHistoryService;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailWorkHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;
        $periodYearStart = substr($data['period_start'], 0, 4);
        $periodMonthStart = substr($data['period_start'], 4);
        $jobTypeId = in_array($data['job_type_id'], WorkHistoryService::getInstance()->getTypeIds(MJobType::query())) ? $data['job_type_id'] : MJobType::OTHER;
        $workTypeId = in_array($data['work_type_id'], WorkHistoryService::getInstance()->getTypeIds(MWorkType::query())) ? $data['work_type_id'] : MWorkType::OTHER;

        $dataWorkHistory = [
            'id' => $data->id,
            'job_types' => [
                'id' => $jobTypeId,
                'name' => @$data['jobType']['name'],
            ],
            'work_types' => [
                'id' => $workTypeId,
                'name' => @$data['workType']['name'],
            ],
            'is_other_job_type' => $jobTypeId == MJobType::OTHER,
            'is_other_work_type' => $workTypeId == MWorkType::OTHER,
            'position_offices' => NameTypeResource::collection($data['position_offices']),
            'position_office_options' => NameTypeResource::collection($data['position_office_options']),
            'store_name' => $data->store_name,
            'company_name' => $data->company_name,
            'period_year_start' => $periodYearStart,
            'period_month_start' => $periodMonthStart,
            'period_start' => $periodYearStart . '/' . $periodMonthStart,
            'business_content' => $data->business_content,
            'experience_accumulation' => $data->experience_accumulation,
        ];

        if ($data['period_end']) {
            $periodYearEnd = substr($data['period_end'], 0, 4);
            $periodMonthEnd = substr($data['period_end'], 4);

            $dataWorkHistory = array_merge($dataWorkHistory, [
                'period_year_end' => $periodYearEnd,
                'period_month_end' => $periodMonthEnd,
                'period_end' => $periodYearEnd . '/' . $periodYearEnd,
            ]);
        }

        return $dataWorkHistory;
    }
}
