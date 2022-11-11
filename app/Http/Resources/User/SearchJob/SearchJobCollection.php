<?php

namespace App\Http\Resources\User\SearchJob;

use App\Helpers\SearchJobHelper;
use App\Models\MJobExperience;
use App\Models\MJobFeature;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Services\User\Job\JobService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchJobCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $paginator = $this->resource;

        $masterData = SearchJobHelper::getJobMasterData();

        foreach ($paginator as $searchJob) {
            $searchJob = SearchJobHelper::addFormatSearchJobJsonData($searchJob, $masterData);
        }

        return [
            'data' => SearchJobResource::collection($paginator),
            'per_page' => $paginator->perPage(),
            'total_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
        ];
    }
}
