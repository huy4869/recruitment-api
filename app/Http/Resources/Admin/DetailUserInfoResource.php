<?php

namespace App\Http\Resources\Admin;

use App\Helpers\DateTimeHelper;
use App\Helpers\UserHelper;
use App\Http\Resources\Recruiter\Job\DetailImageResource;
use App\Http\Resources\User\WorkHistory\NameTypeResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailUserInfoResource extends JsonResource
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
            'position_office_options' => NameTypeResource::collection($this['position_offices']),
            'id' => $this['id'],
            'avatar' => $this['is_public_avatar'] ? $this['avatar_banner'] : null,
            'avatar_details' => $this['is_public_avatar'] == User::STATUS_PUBLIC_AVATAR
                ? DetailImageResource::collection($this['avatar_details'])
                : null,
            'first_name' => $this['first_name'],
            'last_name' => $this['last_name'],
            'furi_first_name' => $this['furi_first_name'],
            'furi_last_name' => $this['furi_last_name'],
            'alias_name' => $this['alias_name'],
            'age' => DateTimeHelper::birthDayByAge($this['birthday'], now()),
            'user_address' => [
                'postal_code' => $this['postal_code'],
                'province_id' => $this['province_id'],
                'province_name' => $this['province'],
                'province_city_id' => $this['province_city_id'],
                'province_city_name' => $this['province_city'],
                'address' => $this['address'],
                'building' => $this['building'],
            ],
            'tel' => $this['tel'],
            'email' => $this['email'],
            'last_login_at' => DateTimeHelper::checkDateLoginAt($this['last_login_at']),
            'facebook' => $this['facebook'],
            'twitter' => $this['twitter'],
            'instagram' => $this['instagram'],
            'line' => $this['line'],
            'birthday' => $this['birthday'],
            'birthday_format' => DateTimeHelper::formatDateJa($this['birthday']),
            'gender' => $this['gender'],
            'gender_id' => $this['gender_id'],
            'user_work_histories' => $this['user_work_histories'],
            'pr' => [
                'favorite_skill' => $this['favorite_skill'],
                'experience_knowledge' => $this['experience_knowledge'],
                'self_pr' => $this['self_pr'],
                'skills' => UserHelper::getSkillUser($this['skills']),
            ],
            'user_learning_histories' => $this['user_learning_histories'],
            'user_licenses_qualifications' => $this['user_licenses_qualifications'],
            'motivation' => [
                'motivation' => $this['motivation'],
                'noteworthy' => $this['noteworthy']
            ],
            'is_public_avatar' => !!$this['is_public_avatar']
        ];
    }
}
