<?php

namespace App\Http\Resources\Recruiter;

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
            'name' => $this->name,
            'recruiter_name' => $this->recruiter_name,
            'tel' => $this->tel,
            'province_id' => $this->province_id,
            'province_name' => $this->province->name ?? null,
            'province_city_id' => $this->province_city_id,
            'province_city_name' => $this->provinceCity->name ?? null,
            'address' => $this->address,
            'specialize_ids' => $this->specialize_ids,
        ];
    }
}
