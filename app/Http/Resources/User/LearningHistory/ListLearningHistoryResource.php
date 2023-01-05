<?php

namespace App\Http\Resources\User\LearningHistory;

use App\Helpers\DateTimeHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListLearningHistoryResource extends JsonResource
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
        $learningStatusName = $data->learning_status_id ? $data->learningStatus->name : null;
        $enrollmentPeriodYearStart = substr($data->enrollment_period_start, 0, 4);
        $enrollmentPeriodMonthStart = substr($data->enrollment_period_start, 4);
        $enrollmentPeriodStart = DateTimeHelper::formatNameDateHalfJa($enrollmentPeriodYearStart, $enrollmentPeriodMonthStart);
        $enrollmentPeriodYearEnd = substr($data->enrollment_period_end, 0, 4);
        $enrollmentPeriodMonthEnd = substr($data->enrollment_period_end, 4);
        $enrollmentPeriodEnd = DateTimeHelper::formatNameDateHalfJa($enrollmentPeriodYearEnd, $enrollmentPeriodMonthEnd);
        $EnrollmentPeriod = sprintf(
            '%s～%s%s',
            $enrollmentPeriodStart,
            $enrollmentPeriodEnd,
            $learningStatusName ? trans('common.learning_status_name', ['status_name' => $learningStatusName]) : null);

        return [
            'id' => $data->id,
            'school_name' => $data->school_name,
            'enrollment_period_format' => $EnrollmentPeriod
        ];
    }
}
