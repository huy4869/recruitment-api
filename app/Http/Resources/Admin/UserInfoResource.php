<?php

namespace App\Http\Resources\Admin;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $desiredConditionUser = $this->desiredConditionUser;

        return [
            'id' => $this->id,
            'avatar' => $this->is_public_avatar == User::STATUS_PUBLIC_AVATAR
                ? FileHelper::getFullUrl(@$this->avatarBanner->url)
                : null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'furi_first_name' => $this->furi_first_name,
            'furi_last_name' => $this->furi_last_name,
            'alias_name' => $this->alias_name,
            'age' => $this->age,
            'user_address' => [
                'postal_code' => $this->postal_code,
                'province_id' => $this->province->id,
                'province_name' => $this->province->name,
                'province_city_id' => $this->province_city_id,
                'province_city_name' => @$this->provinceCity->name,
                'address' => $this->address,
                'building' => $this->building,
            ],
            'tel' => $this->tel,
            'email' => $this->email,
            'last_login_at' => DateTimeHelper::checkDateLoginAt($this->last_login_at),
            'job_types' => $this->job_types,
            'salary' => [
                'id' => @$desiredConditionUser->salaryType->id,
                'type' => @$desiredConditionUser->salaryType->name,
                'min' => @$desiredConditionUser->salary_min,
                'max' => @$desiredConditionUser->salary_max,
            ],
            'address' => [
                'province_id' => @$desiredConditionUser->province->id,
                'province_name' => @$desiredConditionUser->province->name,
            ],
            'job_experiences' => $this->job_experiences,
            'work_types' => $this->work_types,
            'job_features' => $this->job_features,
        ];
    }
}
