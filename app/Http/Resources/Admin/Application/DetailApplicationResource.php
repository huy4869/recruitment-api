<?php

namespace App\Http\Resources\Admin\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Http\Resources\Admin\Application\DetailAvatarResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = $this->applicationUser;

        return [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'avatar_banner' => FileHelper::getFullUrl(@$user->avatarBanner->url),
                'avatar_detail' => DetailAvatarResource::collection($user->avatarDetails),
                'birthday' => DateTimeHelper::formatDateJa($user->birthday),
                'age' => $user->age,
                'gender' => $user->gender->name ?? null,
                'tel' => $user->tel,
                'email' => $user->email,
                'postal_code' => $user->postal_code,
                'address' => [
                    'province_name' => $user->province->name ?? null,
                    'province_city_name' => $user->provinceCity->name ?? null,
                    'address' =>  $user->address,
                    'building' =>  $user->building,
                ]
            ],
            'application_id' => $this->id,
            'job_name' => $this->jobPosting->name,
            'store_name' => $this->store->name,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
            'interview_status' => [
                'id' => $this->interviews->id,
                'name' => $this->interviews->name,
            ],
            'interview_date' => DateTimeHelper::formatDateDayOfWeekJa($this->date) . $this->hours,
            'date' => @explode(' ', $this->date)[0],
            'interview_approach_id' => $this->interview_approach_id,
            'hours' => $this->hours,
            'note' => $this->note,
            'owner_memo' => $this->owner_memo,
        ];
    }
}
