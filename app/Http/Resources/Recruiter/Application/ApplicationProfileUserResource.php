<?php

namespace App\Http\Resources\Recruiter\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Http\Resources\Recruiter\MultipleImageResoure;
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
            'avatar' => $this['avatar_banner'],
            'avatar_details' => MultipleImageResoure::collection($this['avatar_details']),
            'first_name' => @$this['application_user']['first_name'],
            'last_name' => @$this['application_user']['last_name'],
            'furi_first_name' => @$this['application_user']['furi_first_name'],
            'furi_last_name' => @$this['application_user']['furi_last_name'],
            'alias_name' => @$this['application_user']['alias_name'],
            'age' => @$this['application_user']['age'],
            'user_address' => [
                'postal_code' => @$this['application_user']['postal_code'],
                'district_name' => $this['district_name'],
                'province_name' => $this['province'],
                'address' => @$this['application_user']['address'],
                'building' => @$this['application_user']['building'],
            ],
            'tel' => @$this['application_user']['tel'],
            'email' => @$this['application_user']['email'],
            'last_login_at' => DateTimeHelper::formatTimeChat($this['last_login_at']),
            'facebook' => @$this['application_user']['facebook'],
            'twitter' => @$this['application_user']['twitter'],
            'instagram' => @$this['application_user']['instagram'],
            'line' => @$this['application_user']['line'],
            'birthday' => DateTimeHelper::formatDateJa(@$this['application_user']['birthday']),
            'gender' => $this['gender'] ,
            'user_word_histories' => $this['applicationUserWorkHistories'],
            'pr' => [
                'favorite_skill' => @$this['application_user']['favorite_skill'],
                'experience_knowledge' => @$this['application_user']['experience_knowledge'],
                'self_pr' => @$this['application_user']['self_pr'],
            ],
            'user_learning_histories' => $this['applicationLearningHistories'],
            'user_licenses_qualifications' => $this['applicationLicensesQualifications'],
            'motivation' => [
                'motivation' => @$this['application_user']['motivation'],
            ],
        ];
    }
}
