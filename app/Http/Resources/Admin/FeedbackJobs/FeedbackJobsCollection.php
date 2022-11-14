<?php

namespace App\Http\Resources\Admin\FeedbackJobs;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FeedbackJobsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $paginator = $this->resource;

        return [
            'data' => FeedbackJobResource::collection($paginator),
            'per_page' => $paginator->perPage(),
            'total_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
        ];
    }
}
