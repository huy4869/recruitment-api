<?php

namespace App\Http\Resources\Admin\User;

use App\Helpers\DateTimeHelper;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ],
            'stores' => $this->getAllOwnStoreNames(),
            'email' => $this->email,
            'last_login_at' => DateTimeHelper::formatDateTimeJa($this->last_login_at),
        ];
    }
}
