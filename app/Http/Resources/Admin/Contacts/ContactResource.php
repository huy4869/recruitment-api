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

        return [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'be_read' => $data->be_read,
            'content' => $data->content,
            'created_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->created_at),
        ];
    }
}
