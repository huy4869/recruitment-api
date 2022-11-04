<?php

namespace App\Http\Resources\Recruiter;

use Illuminate\Http\Resources\Json\JsonResource;

class DateChatResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $date = date(config('date.month_day'), strtotime($this['date']));

        return [
            $date => ChatDetailResource::collection($this['data']),
        ];
    }
}
