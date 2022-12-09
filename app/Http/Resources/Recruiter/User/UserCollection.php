<?php

namespace App\Http\Resources\Recruiter\User;

use App\Services\Recruiter\User\UserService;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $recruiter = $this['recruiter'];
        $paginator = $this['users']->resource;

        $users = UserService::getUserInfoForListUser($recruiter, $paginator);

        return [
            'data' => UserResource::collection($users),
            'per_page' => $paginator->perPage(),
            'total_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
        ];
    }
}
