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
            'application_tel' => $this->application_tel,
            'address' => [
                'postal_code' => $this->postal_code,
                'province' => $this->province->name ?? null,
                'province_city' => $this->provinceCity->name ??null,
                'address' => $this->address,
                'building' => $this->building,
            ],
            'specialize_ids' => $this->specialize_ids,
            'is_deleted' => !is_null($this->deleted_at),
        ];
    }
}
