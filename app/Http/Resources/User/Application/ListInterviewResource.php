<?php

namespace App\Http\Resources\User\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\MInterviewApproach;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ListInterviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $approach = json_decode($this->interview_approaches);
        $interviewMethod = MInterviewApproach::query()->pluck('name')->toArray();

        return [
            'id' => $this->id,
            'job_banner' => FileHelper::getFullUrl($this->jobPosting->bannerImage->url),
            'job_name' => $this->jobPosting->name,
            'store_name' => $this->store->name,
            'interview_date' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->date),
            'interview_approach' => [
                'id' => $approach->id,
                'method' => $interviewMethod[$approach->id - 1],
                'approach_label' => config('application.interview_approach_label.' . $approach->id),
                'approach' =>  $approach->approach,
            ],
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
