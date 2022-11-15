<?php

namespace App\Http\Resources\Admin\FeedbackJobs;

use App\Helpers\DateTimeHelper;
use App\Models\MFeedbackType;
use App\Services\Admin\FeedbackJobs\FeedbackJobsService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackJobResource extends JsonResource
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
            'email' => $data->user->email,
            'be_read' => $data->be_read,
            'content' => $data->content,
            'feedback_types' => $dataFeedbackType,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
