<?php

namespace App\Http\Resources\User\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class MeResource extends JsonResource
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
            'name' => $data->name,
            'email' => $data->email,
        ];
    }
}
