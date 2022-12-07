<?php

namespace App\Http\Resources\Recruiter\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\UserHelper;
use App\Http\Resources\Recruiter\MultipleImageResoure;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationProfileUserResource extends JsonResource
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
            'id' => $this['user_id'],
            'avatar' => $this['application_user']['is_public_thumbnail'] == User::STATUS_PUBLIC_AVATAR ? $this['avatar_banner'] : null,
            'avatar_details' => $this['application_user']['is_public_thumbnail'] == User::STATUS_PUBLIC_AVATAR
                ? MultipleImageResoure::collection($this['avatar_details'])
                : null,
            'first_name' => @$this['application_user']['first_name'],
            'last_name' => @$this['application_user']['last_name'],
            'furi_first_name' => @$this['application_user']['furi_first_name'],
            'furi_last_name' => @$this['application_user']['furi_last_name'],
            'alias_name' => @$this['application_user']['alias_name'],
            'age' => DateTimeHelper::birthDayByAge(@$this['application_user']['birthday'], @$this['application_user']['created_at']),
            'user_address' => [
                'postal_code' => @$this['application_user']['postal_code'],
                'province_name' => $this['province'],
                'province_city_name' => $this['province_city_name'],
                'address' => @$this['application_user']['address'],
                'building' => @$this['application_user']['building'],
            ],
            'tel' => @$this['application_user']['tel'],
            'email' => @$this['application_user']['email'],
            'last_login_at' => DateTimeHelper::checkDateLoginAt($this['last_login_at']),
            'facebook' => @$this['application_user']['facebook'],
            'twitter' => @$this['application_user']['twitter'],
            'instagram' => @$this['application_user']['instagram'],
            'line' => @$this['application_user']['line'],
            'birthday' => DateTimeHelper::formatDateJa(@$this['application_user']['birthday']),
            'gender' => $this['gender'] ,
            'user_work_histories' => $this['applicationUserWorkHistories'],
            'pr' => [
                'favorite_skill' => @$this['application_user']['favorite_skill'],
                'experience_knowledge' => @$this['application_user']['experience_knowledge'],
                'self_pr' => @$this['application_user']['self_pr'],
                'skills' => UserHelper::getSkillUser(@$this['application_user']['skills']),
            ],
            'user_learning_histories' => $this['applicationLearningHistories'],
            'user_licenses_qualifications' => $this['applicationLicensesQualifications'],
            'motivation' => [
                'motivation' => @$this['application_user']['motivation'],
                'noteworthy' => @$this['application_user']['noteworthy'],
            ],
        ];
    }
}
