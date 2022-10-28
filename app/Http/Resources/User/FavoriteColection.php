<?php

namespace App\Http\Resources\User;

use App\Helpers\JobHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteColection extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $masterData = JobHelper::getJobMasterData($this->user);
        $result = [];

        foreach ($this as $job) {
            $result[] = JobHelper::addFormatFavoriteJsonData($job, $masterData);
        }
        return [
            'paginate' => [
                'currentPage' => $favoriteJob->currentPage(),
                'path' => $favoriteJob->path(),
                'totalPage' => $favoriteJob->lastPage(),
                'total' => $favoriteJob->total(),
            ],
            'favoriteJob' => $result,
        ];
    }
}
