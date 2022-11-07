<?php

namespace App\Http\Resources\Admin\Job;

use App\Services\Recruiter\Job\JobService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JobCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $paginator = $this->resource;

        $jobs = JobService::getJobInfoForListJob($paginator);

        return [
            'data' => JobResource::collection($jobs),
            'per_page' => $paginator->perPage(),
            'total_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
        ];
    }
}
