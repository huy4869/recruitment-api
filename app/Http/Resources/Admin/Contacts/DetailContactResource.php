<?php

namespace App\Http\Resources\Admin\Contacts;

use App\Helpers\DateTimeHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailContactResource extends JsonResource
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
            'store_id' => $data->store_id,
            'name' => $data->name,
            'email' => $data->email,
            'tel' => $data->tel,
            'content' => $data->content,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
