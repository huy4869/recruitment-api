<?php

namespace App\Services\Admin\Store;

use App\Exceptions\InputException;
use App\Helpers\StringHelper;
use App\Models\Store;
use App\Services\Common\SearchService;
use App\Services\TableService;

class StoreTableService extends TableService
{
    protected $filterables = [
        'province_id' => 'filterTypes',
        'province_city_id' => 'filterTypes',
        'specialize_ids' => 'filterTypes',
        'store_name' => 'filterTypes',
        'recruiter_name' => 'filterTypes',
    ];

    public function filterTypes($query, $filter)
    {
        if (!count($filter)) {
            return $query;
        }

        $jsonKey = [
            'specialize_ids',
        ];

        if (!isset($filter['key']) || !isset($filter['data'])) {
            throw new InputException(trans('response.invalid'));
        }

        if (in_array($filter['key'], $jsonKey)) {
            SearchService::queryJsonKey($query, $filter);
        }

        if ($filter['key'] == 'province_id' || $filter['key'] == 'province_city_id') {
            $query->where($filter['key'], $filter['data']);
        }

        if ($filter['key'] == 'store_name') {
            $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);
            $query->where('name', 'like', '%' . trim($filter['data']) . '%');
        }

        if ($filter['key'] == 'recruiter_name') {
            $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);
            $query->where('recruiter_name', 'like', '%' . trim($filter['data']) . '%');
        }

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
            building,
            address,
            specialize_ids';
    }
}
