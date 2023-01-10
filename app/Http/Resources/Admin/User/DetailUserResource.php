<?php

namespace App\Http\Resources\Admin\User;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailUserResource extends JsonResource
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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'furi_first_name' => $this->furi_last_name,
            'furi_last_name' => $this->furi_last_name,
            'role' => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ],
            'stores' => UserStoresResource::collection($this->stores),
            'email' => $this->email,
            'last_login_at' => DateTimeHelper::formatDateTimeJa($this->last_login_at),
            'is_deleted' => !is_null($this->deleted_at)
        ];
    }
}
