<?php

namespace App\Services\Recruiter\Store;

use App\Exceptions\InputException;
use App\Models\Store;
use App\Services\TableService;

class StoreTableService extends TableService
{
    const FIRST_ARRAY = 0;

    protected $filterables =[
        'province_ids' => 'filterTypes',
        'province_city_ids' => 'filterTypes',
        'specialize_ids' => 'filterTypes',
        'store_name' => 'filterTypes',
        'recruiter_name' => 'filterTypes',
    ];

    protected function filterTypes($query, $filter, $filters)
    {
        if (!count($filters)) {
            return $query;
        }

        foreach ($filters as $filterItem) {
            if (!isset($filterItem['key']) || !isset($filterItem['data'])) {
                throw new InputException(trans('response.invalid'));
            }

            if ($filterItem['key'] == 'specialize_ids') {
                $query->where(function ($query) use ($filterItem) {
                    $types = json_decode($filterItem['data']);
                    $query->whereJsonContains($filterItem['key'], $types[self::FIRST_ARRAY]);
                    unset($types[self::FIRST_ARRAY]);

                    foreach ($types as $type) {
                        $query->orWhereJsonContains($filterItem['key'], $type);
                    }
                });
            }

            if ($filterItem['key'] == 'province_ids') {
                $query->where(function ($query) use ($filterItem) {
                    $provinceIds = json_decode($filterItem['data']);

                    foreach ($provinceIds as $id) {
                        $query->orWhere('province_id', $id);
                    }
                });
            }

            if ($filterItem['key'] == 'province_city_ids') {
                $query->where(function ($query) use ($filterItem) {
                    $provinceCityIds = json_decode($filterItem['data']);

                    foreach ($provinceCityIds as $id) {
                        $query->orWhere('province_city_id', $id);
                    }
                });
            }

            if ($filterItem['key'] == 'store_name') {
                $query->where('name', 'like', '%' . $filterItem['data'] . '%');
            }

            if ($filterItem['key'] == 'recruiter_name') {
                $query->where('recruiter_name', 'like', '%' . $filterItem['data'] . '%');
            }
        }//end foreach

        return $query;
    }


    public function makeNewQuery()
    {
        $rec = $this->user;

         return Store::with([
                'province',
                'province.provinceDistrict',
                'provinceCity'
            ])
            ->where('user_id', $rec->id)
            ->selectRaw($this->getSelectRaw())
            ->orderByDesc('created_at');
    }

    /**
     * select store
     * @return string
     */
    protected function getSelectRaw()
    {
        return 'id,
            name,
            tel,
            province_id,
            province_city_id,
            recruiter_name,
            postal_code,
            building,
            address,
            specialize_ids';
    }
}
