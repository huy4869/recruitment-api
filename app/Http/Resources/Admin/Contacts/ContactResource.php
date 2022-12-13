<?php

namespace App\Http\Resources\Admin\Contacts;

use App\Helpers\DateTimeHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
        if (is_null($data->user_id)) {
            $name = $data->name;
            $email = $data->email;
            $tel = $data->tel;
        } else {
            $name = $data->user->first_name . $data->user->last_name;
            $email = $data->user->email;
            $tel = $data->user->tel;
        }

        return [
            'id' => $data->id,
            'name' => $name,
            'email' => $email,
            'tel' => $tel,
            'be_read' => $data->be_read,
            'content' => $data->content,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
