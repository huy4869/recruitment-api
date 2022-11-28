<?php

namespace App\Http\Resources\User;

use App\Helpers\UserHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InformationPrResource extends JsonResource
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

        return [
            'id' => $data->id,
            'favorite_skill' => $data->favorite_skill,
            'experience_knowledge' => $data->experience_knowledge,
            'self_pr' => $data->self_pr,
            'skills' => UserHelper::getSkillUser($data->skills),
        ];
    }
}
