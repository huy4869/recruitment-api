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
            'avatar' => $this['application_user_trash']['is_public_avatar'] == User::STATUS_PUBLIC_AVATAR ? FileHelper::getFullUrl($this['avatar_banner']) : null,
            'avatar_details' => $this['application_user_trash']['is_public_avatar'] == User::STATUS_PUBLIC_AVATAR ? MultipleImageResoure::collection($this['avatar_details']) : null,
            'first_name' => @$this['application_user_trash']['first_name'],
            'last_name' => @$this['application_user_trash']['last_name'],
            'furi_first_name' => @$this['application_user_trash']['furi_first_name'],
            'furi_last_name' => @$this['application_user_trash']['furi_last_name'],
            'alias_name' => @$this['application_user_trash']['alias_name'],
            'age' => DateTimeHelper::birthDayByAge(@$this['application_user_trash']['birthday'], @$this['application_user_trash']['created_at']),
            'user_address' => [
                'postal_code' => @$this['application_user_trash']['postal_code'],
                'province_name' => $this['province'],
                'province_city_name' => $this['province_city_name'],
                'address' => @$this['application_user_trash']['address'],
                'building' => @$this['application_user_trash']['building'],
            ],
            'tel' => @$this['application_user_trash']['tel'],
            'email' => @$this['application_user_trash']['email'],
            'last_login_at' => DateTimeHelper::checkDateLoginAt($this['last_login_at']),
            'facebook' => @$this['application_user_trash']['facebook'],
            'twitter' => @$this['application_user_trash']['twitter'],
            'instagram' => @$this['application_user_trash']['instagram'],
            'line' => @$this['application_user_trash']['line'],
            'birthday' => DateTimeHelper::formatDateJa(@$this['application_user_trash']['birthday']),
            'gender' => $this['gender'] ,
            'user_work_histories' => $this['applicationUserWorkHistories'],
            'pr' => [
                'favorite_skill' => @$this['application_user_trash']['favorite_skill'],
                'experience_knowledge' => @$this['application_user_trash']['experience_knowledge'],
                'self_pr' => @$this['application_user_trash']['self_pr'],
                'skills' => UserHelper::getSkillUser(@$this['application_user_trash']['skills']),
            ],
            'user_learning_histories' => $this['applicationLearningHistories'],
            'user_licenses_qualifications' => $this['applicationLicensesQualifications'],
            'motivation' => [
                'motivation' => @$this['application_user_trash']['motivation'],
                'noteworthy' => @$this['application_user_trash']['noteworthy'],
            ],
            'is_deleted_at' => !is_null($this['deleted_at']),
        ];
    }
}
