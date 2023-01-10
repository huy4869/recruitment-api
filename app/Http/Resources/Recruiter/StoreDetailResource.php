<?php

namespace App\Http\Resources\Recruiter;

use App\Helpers\FileHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $month = substr($this->founded_year, 4);
        $year = substr($this->founded_year, 0, 4);
        $founded_year = sprintf('%s%s%s%s', $year, trans('common.year'), $month, trans('common.month'));

        return [
            'url' => FileHelper::getFullUrl($this->storeBanner->url ?? null),
            'store_name' => $this->name,
            'website' => $this->website,
            'tel' => $this->tel,
            'application_tel' => $this->application_tel,
            'address' => [
                'postal_code' => $this->postal_code,
                'province_id' =>$this->province_id,
                'province' => $this->province->name ?? null,
                'province_city_id' => $this->province_city_id,
                'province_city_name' => $this->provinceCity->name ?? null,
                'address' => $this->address,
                'building' => $this->building,
            ],
            'manager_name' => $this->manager_name,
            'employee_quantity' => $this->employee_quantity,
            'date' => [
                'founded_year' => $founded_year,
                'year' => $year,
                'month' => $month,
            ],
            'business_segment' => $this->business_segment,
            'specialize_ids' => $this->specialize_ids,
            'recruiter_name' => $this->recruiter_name,
            'store_features' => $this->store_features,
            'is_deleted' => !is_null($this->deleted_at),
        ];
    }
}
