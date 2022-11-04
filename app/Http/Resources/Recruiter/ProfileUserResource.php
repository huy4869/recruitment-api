<?php

namespace App\Http\Resources\Recruiter;

use App\Helpers\DateTimeHelper;
use App\Http\Resources\Recruiter\Job\DetailImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileUserResource extends JsonResource
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
            'id' => $this['id'],
            'avatar' => $this['avatar_banner'],
            'avatar_details' => DetailImageResource::collection($this['avatar_details']),
            'first_name' => $this['first_name'],
            'last_name' => $this['last_name'],
            'furi_first_name' => $this['furi_first_name'],
            'furi_last_name' => $this['furi_last_name'],
            'alias_name' => $this['alias_name'],
            'age' => $this['age'],
            'user_address' => [
                'postal_code' => $this['postal_code'],
                'district_name' => $this['district_name'],
                'province_name' => $this['province'],
                'city' => $this['city'],
                'address' => $this['address'],
            ],
            'tel' => $this['tel'],
            'email' => $this['email'],
            'last_login_at' => DateTimeHelper::formatTimeChat($this['last_login_at']),
            'facebook' => $this['facebook'],
            'twitter' => $this['twitter'],
            'instagram' => $this['instagram'],
            'line' => $this['line'],
            'birthday' => DateTimeHelper::formatDateJa($this['birthday']),
            'gender' => $this['gender'],
            'user_word_histories' => $this['user_word_histories'],
            'pr' => [
                'favorite_skill' => $this['favorite_skill'],
                'experience_knowledge' => $this['experience_knowledge'],
                'self_pr' => $this['self_pr'],
            ],
            'user_learning_histories' => $this['user_learning_histories'],
            'user_licenses_qualifications' => $this['user_licenses_qualifications'],
            'motivation' => [
                'motivation' => $this['motivation'],
            ],
        ];
    }
}