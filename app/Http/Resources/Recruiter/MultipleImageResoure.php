<?php

namespace App\Http\Resources\Recruiter;

use App\Helpers\FileHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class MultipleImageResoure extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'url' => FileHelper::getFullUrl($this['url']),
        ];
    }
}
