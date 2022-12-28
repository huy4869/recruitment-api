<?php

namespace App\Http\Resources\Admin\FeedbackJobs;

use App\Helpers\DateTimeHelper;
use App\Models\MFeedbackType;
use App\Services\Admin\FeedbackJobs\FeedbackJobsService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailFeedbackJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;
        $dataFeedbackType = FeedbackJobsService::getDataObject($data->feedback_type_ids, MFeedbackType::query());

        return [
            'id' => $data->id,
            'job_posting_id' => $data->job_posting_id,
            'email' => $data->user->email,
            'name' => $data->user->first_name . $data->user->last_name,
            'content' => $data->content,
            'feedback_types' => $dataFeedbackType,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
