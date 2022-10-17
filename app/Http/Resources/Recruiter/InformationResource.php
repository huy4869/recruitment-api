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
        $capitalStock = $this->capital_stock/config('common.capital_stock') . trans('common.capital_stock');
        $month = substr($this->founded_year, 4);
        $year = substr($this->founded_year, 0, 4);
        $founded_year = $year . trans('common.year') . $month . trans('common.month');
        $employeeQuantity = $this->employee_quantity . trans('common.employee_quantity');
        $storeName = [];
        foreach ($this->stores as $store) {
            $storeName[] = $store->name;
        }

        return [
            'store_name' => $storeName,
            'company_name' => $this->company_name,
            'home_page_rescuiter' => $this->home_page_recruiter,
            'tel' => $this->tel,
            'address_information' => [
                'postal_code' => $this->postal_code,
                'province_name' => $this->province->name ?? '',
                'city' => $this->city,
                'address' => $this->address,
                'full_address' => $this->fullNameAddress,
            ],
            'representative_name' =>$this->alias_name,
            'employee_quantity' => $employeeQuantity,
            'date' => [
                'founded_year' => $founded_year,
                'year' => $year,
                'month' => $month,
            ],
            'capital_stock' => $capitalStock,
            'manager_name' => $this->manager_name,
            'line_id' => $this->line,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'twitter' => $this->twitter,
        ];
    }
}
