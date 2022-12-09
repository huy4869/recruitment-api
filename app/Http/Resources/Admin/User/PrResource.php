<?php

namespace App\Http\Resources\Admin\User;

use App\Helpers\UserHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class PrResource extends JsonResource
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
            'id' => $this->id,
            'favorite_skill' => $this->favorite_skill,
            'experience_knowledge' => $this->experience_knowledge,
            'self_pr' => $this->self_pr,
            'skills' => UserHelper::getSkillUser($this->skills),
        ];
    }
}
