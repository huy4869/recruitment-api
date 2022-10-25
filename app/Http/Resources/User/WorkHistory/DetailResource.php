<?php

namespace App\Http\Resources\User\WorkHistory;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
         $data = $this->resource;
         $periodYearStart = substr($data['period_start'], 0, 4);
         $periodMonthStart = substr($data['period_start'], 4);

        $dataWorkHistory = [
            'id' => $data->id,
            'job_type_id' => $data['job_type_id'],
            'job_type_name' => @$data['jobType']['name'],
            'work_type_id' => $data['work_type_id'],
            'work_type_name' => @$data['workType']['name'],
            'position_offices' => NameTypeResource::collection($data['position_offices']),
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
