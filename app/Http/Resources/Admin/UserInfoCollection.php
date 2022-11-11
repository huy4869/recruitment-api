<?php

namespace App\Http\Resources\Admin;

use App\Services\Admin\User\UserService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserInfoCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $paginator = $this->resource;

        $users = UserService::appendMasterDataForUser($paginator);

        return [
            'data' => UserInfoResource::collection($users),
            'per_page' => $paginator->perPage(),
            'total_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
        ];
    }
}
