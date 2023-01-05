<?php

namespace App\Http\Resources\User;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\UserHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;
        if (!$data->full_name && !$data->full_name_furi) {
            $fullName = '';
        } elseif ($data->full_name && $data->full_name_furi) {
            $fullName = $data->full_name . '(' . $data->full_name_furi . ')';
        } else {
            $fullName = $data->full_name;
        }

        return [
            'id' => $data->id,
            'email' => $data->email,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'furi_first_name' => $data->furi_first_name,
            'furi_last_name' => $data->furi_last_name,
            'full_name' => $data->full_name,
            'full_name_furi' => $data->full_name_furi,
            'full_name_user' => $fullName,
            'alias_name' => $data->alias_name,
            'birthday' => $data->birthday,
            'birthday_format' => DateTimeHelper::formatDateJa($data->birthday),
            'age' => DateTimeHelper::birthDayByAge($data->birthday, now()),
            'gender_id' => $data->gender_id,
            'gender_name' => @$data->gender->name,
            'tel' => $data->tel,
            'line' => $data->line,
            'facebook' => $data->facebook,
            'instagram' => $data->instagram,
            'twitter' => $data->twitter,
            'postal_code' => $data->postal_code,
            'province_id' => @$data->provinceCity->province->id,
            'province_name' => @$data->provinceCity->province->name,
            'province_city_id' => $data->province_city_id,
            'province_city_name' => @$data->provinceCity->name,
            'address' => $data->address,
            'building' => $data->building,
            'avatar' => FileHelper::getFullUrl(@$data->avatarBanner->url),
            'images' => ImagesResource::collection($data->avatarDetails),
            'is_public_avatar' => !!$data->is_public_avatar,
        ];
    }
}
