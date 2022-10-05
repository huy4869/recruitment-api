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

        return [
            'id' => $data->id,
            'email' => $data->email,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'furi_first_name' => $data->furi_first_name,
            'furi_last_name' => $data->furi_last_name,
            'full_name' => $data->full_name,
            'full_name_furi' => $data->full_name_furi,
            'full_name_user' => $data->full_name . '(' . $data->full_name_furi . ')',
            'alias_name' => $data->alias_name,
            'birthday' => $data->birthday,
            'birthday_format' => DateTimeHelper::formatDateJa($data->birthday),
            'age' => $data->age,
            'gender_id' => $data->gender_id,
            'gender_name' => $data->gender->name,
            'tel' => $data->tel,
            'line' => $data->line,
            'facebook' => $data->facebook,
            'instagram' => $data->instagram,
            'twitter' => $data->twitter,
            'postal_code' => $data->postal_code,
            'province_id' => $data->province_id,
            'province_name'  => $data->province->name,
            'city' => $data->city,
            'address' => $data->address,
            'avatar' => FileHelper::getFullUrl($data->avatar),
            'images' => ImagesResource::collection($data->images)
        ];
    }
}
