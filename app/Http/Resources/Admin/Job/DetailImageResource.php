<?php

namespace App\Http\Resources\Admin\Job;

use App\Helpers\FileHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'url' => FileHelper::getFullUrl($this->url),
        ];
    }
}
