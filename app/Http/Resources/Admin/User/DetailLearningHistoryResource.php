<?php

namespace App\Http\Resources\Admin\User;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailLearningHistoryResource extends JsonResource
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
        $learningStatusName = @$data->learningStatus->name;
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
            'learning_status_id' => $data->learning_status_id,
            'learning_status_name' => $learningStatusName,
            'school_name' => $data->school_name,
            'enrollment_period_year_start' => $enrollmentPeriodYearStart,
            'enrollment_period_month_start' => $enrollmentPeriodMonthStart,
            'enrollment_period_start' => $enrollmentPeriodYearStart . '/' . $enrollmentPeriodMonthStart,
            'enrollment_period_year_end' => $enrollmentPeriodYearEnd,
            'enrollment_period_month_end' => $enrollmentPeriodMonthEnd,
            'enrollment_period_end' => $enrollmentPeriodYearEnd . '/' . $enrollmentPeriodMonthEnd,
            'enrollment_period_format' => $EnrollmentPeriod
        ];
    }
}
