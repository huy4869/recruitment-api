<?php

namespace App\Http\Resources\Recruiter\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Application;
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
                'gender' => $user->gender->name,
                'tel' => $user->tel,
                'email' => $user->email,
                'postal_code' => $user->postal_code,
                'address' => [
                    'province_district' => $user->province->provinceDistrict->name,
                    'province' => $user->province->name,
                    'province_city' => @$user->provinceCity->name,
                    'address' =>  $user->address,
                    'building' =>  $user->building,
                ]
            ],
            'job_name' => $this->jobPosting->name,
            'store_name' => $this->store->name,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
            'interview_status' => [
                'id' => $this->interviews->id,
                'name' => $this->interviews->name,
            ],
            'can_change_status' => $this->interview_status_id != Application::STATUS_REJECTED,
            'interview_date' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->date),
            'note' => $this->note,
        ];
    }
}
