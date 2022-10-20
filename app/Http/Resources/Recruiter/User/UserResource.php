<?php

namespace App\Http\Resources\Recruiter\User;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\UrlHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use function Symfony\Component\String\s;

class UserResource extends JsonResource
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
            'avatar' => FileHelper::getFullUrl($this->avatarBanner->url),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'furi_first_name' => $this->furi_first_name,
            'furi_last_name' => $this->furi_last_name,
            'alias_name' => $this->alias_name,
            'age' => $this->age,
            'user_address' => [
                'postal_code' => $this->postal_code,
                'district_id' => $this->province->provinceDistrict->id,
                'district_name' => $this->province->provinceDistrict->name,
                'province_id' => $this->province->id,
                'province_name' => $this->province->name,
                'city' => $this->city,
                'address' => $this->address,
            ],
            'tel' => $this->tel,
            'email' => $this->email,
            'last_login_at' => DateTimeHelper::parseToDiffForHumansJa($this->last_login_at),
            'job_types' => $this->job_types,
            'salary' => [
                'salary_id' => $desiredConditionUser->salaryType->id,
                'salary_type' => $desiredConditionUser->salaryType->name,
                'salary_min' => $desiredConditionUser->salary_min,
                'salary_max' => $desiredConditionUser->salary_max,
            ],
            'address' => [
                'district_id' => $desiredConditionUser->province->provinceDistrict->id,
                'district_name' => $desiredConditionUser->province->provinceDistrict->name,
                'province_id' => $desiredConditionUser->province->id,
                'province_name' => $desiredConditionUser->province->name,
            ],
            'job_experiences' => $this->job_experiences,
            'work_types' => $this->work_types,
            'job_features' => $this->job_features,
            'is_favorite' => $this->favorite,
        ];
    }
}
