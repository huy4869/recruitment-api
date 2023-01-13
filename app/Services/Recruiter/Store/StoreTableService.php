<?php

namespace App\Services\Recruiter\Store;

use App\Exceptions\InputException;
use App\Helpers\StringHelper;
use App\Models\Store;
use App\Services\Common\SearchService;
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

    protected function filterTypes($query, $filter)
    {
        if (!count($filter)) {
            return $query;
        }

        if (!isset($filter['key']) || !isset($filter['data'])) {
            throw new InputException(trans('response.invalid'));
        }

        if ($filter['key'] == 'specialize_ids') {
            $query->where(function ($query) use ($filter) {
                $types = SearchService::encodeStringToArray($filter['data']);
                $query->whereJsonContains($filter['key'], $types[self::FIRST_ARRAY]);
                unset($types[self::FIRST_ARRAY]);

                foreach ($types as $type) {
                    $query->orWhereJsonContains($filter['key'], $type);
                }
            });
        }

        if ($filter['key'] == 'province_ids') {
            $query->where(function ($query) use ($filter) {
                $provinceIds = SearchService::encodeStringToArray($filter['data']);

                foreach ($provinceIds as $id) {
                    $query->orWhere('province_id', $id);
                }
            });
        }

        if ($filter['key'] == 'province_city_ids') {
            $query->where(function ($query) use ($filter) {
                $provinceCityIds = SearchService::encodeStringToArray($filter['data']);

                foreach ($provinceCityIds as $id) {
                    $query->orWhere('province_city_id', $id);
                }
            });
        }

        if ($filter['key'] == 'store_name') {
            $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);
            $query->where('name', 'like', '%' . $filter['data'] . '%');
        }

        if ($filter['key'] == 'recruiter_name') {
            $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);
            $query->where('recruiter_name', 'like', '%' . $filter['data'] . '%');
        }

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
            application_tel,
            province_id,
            province_city_id,
            recruiter_name,
            postal_code,
            building,
            address,
            deleted_at,
            specialize_ids';
    }
}
