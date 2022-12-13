<?php

namespace App\Http\Resources\Admin\Contacts;

use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = $this->resource;
        $paginator = $data['data'];

        if ($data['role_id'] == User::ROLE_USER) {
            $resource = ContactResource::collection($paginator);
        } else {
            $resource = ContactStoreResource::collection($paginator);
        }

        return [
            'data' => $resource,
            'per_page' => $paginator->perPage(),
            'total_page' => $paginator->lastPage(),
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
        ];
    }
}
