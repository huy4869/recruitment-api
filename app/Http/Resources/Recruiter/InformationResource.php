<?php

namespace App\Http\Resources\Recruiter;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class InformationResource extends JsonResource
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
        $founded_year = $year . trans('common.year') . $month . trans('common.month');

        $storeName = [];
        foreach ($this->stores as $store) {
            $storeName[] = $store->name;
        }

        return [
            'store_name' => $storeName,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'furi_first_name' => $this->furi_first_name,
            'furi_last_name' => $this->furi_last_name,
            'company_name' => $this->company_name,
            'home_page_recruiter' => $this->home_page_recruiter,
            'tel' => $this->tel,
            'address_information' => [
                'postal_code' => $this->postal_code,
                'province_id' => $this->province->id ?? null,
                'province_name' => $this->province->name ?? null,
                'province_city_id' => $this->province_city_id ?? null,
                'province_city_name' => $this->provinceCity->name ?? null,
                'address' => $this->address,
                'building' => $this->building,
            ],
            'alias_name' =>$this->alias_name,
            'employee_quantity' => $this->employee_quantity,
            'date' => [
                'founded_year' => $this->founded_year ? $founded_year : null,
                'year' => $year,
                'month' => $month,
            ],
            'capital_stock' => $this->capital_stock,
            'manager_name' => $this->manager_name,
            'line_id' => $this->line,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'twitter' => $this->twitter,
        ];
    }
}
