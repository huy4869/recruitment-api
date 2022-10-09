<?php

namespace App\Http\Resources\User\WorkHistory;

use App\Helpers\DateTimeHelper;
use Carbon\Carbon;
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

        return [
            'id' => $data->id,
            'job_types' => NameTypeResource::collection($data['job_types']),
            'work_types' => NameTypeResource::collection($data['work_types']),
            'position_offices' => NameTypeResource::collection($data['position_offices']),
            'store_name' => $data->store_name,
            'company_name' => $data->company_name,
            'period_start' => DateTimeHelper::formatDateHalfJaFe($data['period_start']),
            'period_end' => DateTimeHelper::formatDateHalfJaFe($data['period_end']),
            'business_content' => $data->business_content,
            'experience_accumulation' => $data->experience_accumulation,
        ];
    }
}
