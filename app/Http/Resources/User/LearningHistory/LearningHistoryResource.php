<?php

namespace App\Http\Resources\User\LearningHistory;

use App\Helpers\DateTimeHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningHistoryResource extends JsonResource
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
        $learningStatusName = $data->learningStatus->name;
        $enrollmentPeriodStart = DateTimeHelper::formatDateHalfJa($data->enrollment_period_start);
        $enrollmentPeriodEnd = DateTimeHelper::formatDateHalfJa($data->enrollment_period_end);
        $EnrollmentPeriod = sprintf('%s ~ %s (%s)', $enrollmentPeriodStart, $enrollmentPeriodEnd, $learningStatusName);

        return [
            'id' => $data->id,
            'learning_status_id' => $data->learning_status_id,
            'learning_status_name' => $learningStatusName,
            'school_name' => $data->school_name,
            'enrollment_period_start' => DateTimeHelper::formatDateHalfJaFe($data->enrollment_period_start),
            'enrollment_period_end' => DateTimeHelper::formatDateHalfJaFe($data->enrollment_period_end),
            'Enrollment_period_format' => $EnrollmentPeriod
        ];
    }
}
