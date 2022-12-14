<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'recruiter_name' => $this->recruiter_name,
            'tel' => $this->tel,
            'application_tel' => $this->application_tel,
            'address' => [
                'postal_code' => $this->postal_code,
                'province' => $this->province->name,
                'province_city' => $this->provinceCity->name,
                'address' => $this->address,
                'building' => $this->building,
            ],
            'specialize_ids' => $this->specialize_ids,
        ];
    }
}
