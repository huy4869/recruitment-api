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
        $periodYearStart = substr($data['period_start'], 0, 4);
        $periodMonthStart = substr($data['period_start'], 4);
        $periodStart = DateTimeHelper::formatNameDateHalfJa($periodYearStart, $periodMonthStart);
        $periodYearEnd = substr($data['period_end'], 0, 4);
        $periodMonthEnd = substr($data['period_end'], 4);
        $periodEnd = DateTimeHelper::formatNameDateHalfJa($periodYearEnd, $periodMonthEnd);

        if ($periodEnd) {
            $periodFullFormat = $periodStart . '～' . $periodEnd;
        } else {
            $periodFullFormat = $periodStart . '～' . '現在';
        }

        return [
            'id' => $data['id'],
            'job_type_name' => $data['job_type']['name'],
            'work_type_name' => $data['work_type']['name'],
            'position_offices' => $data['position_offices'],
            'store_name' => $data['store_name'],
            'company_name' => $data['company_name'],
            'period_full_format' => $periodFullFormat,
            'business_content' => $data['business_content'],
            'experience_accumulation' => $data['experience_accumulation'],
            'created_at' => Carbon::parse($data['created_at'])->toDateTimeString(),
        ];
    }
}
