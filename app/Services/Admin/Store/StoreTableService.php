<?php

namespace App\Services\Admin\Store;

use App\Exceptions\InputException;
use App\Models\Store;
use App\Services\TableService;

class StoreTableService extends TableService
{
    const FIRST_ARRAY = 0;
    protected $filterables = [
        'province_id' => 'filterTypes',
        'specialize_ids' => 'filterTypes',
        'store_name' => 'filterTypes',
        'recruiter_name' => 'filterTypes',
    ];

    public function filterTypes($query, $filter, $filters)
    {
        if (!count($filters)) {
            return $query;
        }

        $jsonKey = [
            'specialize_ids',
        ];

        foreach ($filters as $filterItem) {
            if (!isset($filterItem['key']) || !isset($filterItem['data'])) {
                throw new InputException(trans('response.invalid'));
            }

            if (in_array($filterItem['key'], $jsonKey)) {
                $query->where(function ($query) use ($filterItem) {
                    $types = json_decode($filterItem['data']);

                    if ($types) {
                        $query->whereJsonContains($filterItem['key'], $types[self::FIRST_ARRAY]);
                        unset($types[self::FIRST_ARRAY]);

                        foreach ($types as $type) {
                            $query->whereJsonContains($filterItem['key'], $type);
                        }
                    }
                });
            }

            if ($filterItem['key'] == 'province_id') {
                $query->where('province_id', $filterItem['data']);
            }

            if ($filterItem['key'] == 'store_name') {
                $query->where('name', 'like', '%' . trim($filterItem['data']) . '%');
            }

            if ($filterItem['key'] == 'recruiter_name') {
                $query->where('recruiter_name', 'like', '%' . trim($filterItem['data']) . '%');
            }
        }//end foreach

        return $query;
    }

    public function makeNewQuery()
    {
        return Store::query()
            ->with([
                'province',
                'province.provinceDistrict',
                'provinceCity'
            ])
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
            city,
            address,
            specialize_ids';
    }
}
