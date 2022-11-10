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
            'address' => [
                'district' => $this->province->provinceDistrict->name,
                'province' => $this->province->name,
                'province_city' => $this->provinceCity->name,
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
        ];
    }
}
