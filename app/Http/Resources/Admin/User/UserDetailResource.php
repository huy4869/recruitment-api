<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;

        return [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
        ];
    }
}
