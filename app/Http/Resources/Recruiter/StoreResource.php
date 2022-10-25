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
            'id' => $this->id,
            'name' => $this->name,
            'recruiter_name' => $this->recruiter_name,
            'tel' => $this->tel,
            'address' => [
                'district' => $this->province->provinceDistrict->name,
                'province' => $this->province->name,
                'province_city' => $this->provinceCity->name,
                'city' => $this->city,
                'address' => $this->address,
            ],
            'specialize_ids' => $this->specialize_ids,
        ];
    }
}
