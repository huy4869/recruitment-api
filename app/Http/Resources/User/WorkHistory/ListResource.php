<?php

namespace App\Http\Resources\User\WorkHistory;

use App\Helpers\DateTimeHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
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

        return [
            'id' => $data->id,
            'job_type_id' => $data->job_type_id,
            'job_type_name' => $data->jobType->name,
            'work_type_id' => $data->work_type_id,
            'work_type_name' => $data->workType->name,
            'store_name' => $data->store_name,
            'company_name' => $data->company_name,
            'period_start' => $data->period_start,
            'period_start_format' => DateTimeHelper::formatDateHalfJa($data->period_start),
            'period_end' => $data->period_end,
            'period_end_format' => DateTimeHelper::formatDateHalfJa($data->period_end),
            'period_full_format' => DateTimeHelper::formatDateHalfJa($data->period_start) . ' ~ ' . DateTimeHelper::formatDateHalfJa($data->period_end),
            'position_offices' => $data->position_offices,
            'business_content' => $data->business_content,
            'experience_accumulation' => $data->experience_accumulation,
            'created_at' => Carbon::parse($data->created_at)->toDateTimeString(),
        ];
    }
}
