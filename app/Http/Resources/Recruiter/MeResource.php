<?php

namespace App\Http\Resources\Recruiter;

use App\Helpers\DateTimeHelper;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'furi_first_name' => $this->furi_first_name,
            'furi_last_name' => $this->furi_last_name,
            'alias_name' => $this->alias_name,
            'tel' => $this->tel,
            'email' => $this->email,
            'last_login_at' => DateTimeHelper::formatDateDayOfWeekTimeJa($this->last_login_at),
        ];
    }
}
