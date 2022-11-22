<?php

namespace App\Services\Recruiter;

use App\Models\User;
use App\Services\Service;

class ProfileService extends Service
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getInformation()
    {
        return User::with('stores', 'province')->where('id', $this->user->id)->get();
    }

    /**
     * update information
     *
     * @param $data
     * @return bool
     */
    public function updateInformation($data)
    {
        return $this->user->update([
            'company_name' => $data['company_name'],
            'home_page_rescuiter' => $data['home_page_rescuiter'],
            'tel' => $data['tel'],
            'postal_code' => $data['postal_code'],
            'province_id' => $data['province_id'],
            'building' => $data['building'],
            'address' => $data['address'],
            'alias_name' => $data['alias_name'],
            'employee_quantity' => $data['employee_quantity'],
            'founded_year' => str_replace('/', '', $data['founded_year']),
            'capital_stock' => $data['capital_stock'],
            'manager_name' => $data['manager_name'],
            'line' => $data['line'],
            'facebook' => $data['facebook'],
            'instagram' => $data['instagram'],
            'twitter' => $data['twitter'],
            'province_city_id' => $data['province_city_id'],
        ]);
    }
}
