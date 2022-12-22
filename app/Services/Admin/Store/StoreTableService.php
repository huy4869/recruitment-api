<?php

namespace App\Services\Admin\Store;

use App\Exceptions\InputException;
use App\Helpers\StringHelper;
use App\Models\Store;
use App\Services\Common\SearchService;
use App\Services\TableService;
use Illuminate\Support\Facades\DB;

class StoreTableService extends TableService
{
    protected $filterables = [
        'province_ids' => 'filterTypes',
        'province_city_ids' => 'filterTypes',
        'specialize_ids' => 'filterTypes',
        'store_name' => 'filterTypes',
        'recruiter_name' => 'filterTypes',
        'owner' => 'filterName',
        'stores.id' => 'stores.id',
    ];

    public function filterName($query, $filter)
    {
        if (!count($filter) || !is_string($filter['data'])) {
            return $query;
        }

        $queryKeys = [
            'users.first_name',
            'users.last_name',
            'CONCAT(users.first_name, " ",users.last_name)',
        ];

        $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);
        $content = '%' . trim($filter['data']) . '%';
        $query->where(function ($q) use ($content, $queryKeys) {
            foreach ($queryKeys as $key) {
                $q->orWhere(DB::raw($key), 'like', $content);
            }
        });

        return $query;
    }

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

        if ($filter['key'] == 'province_ids') {
            $query->where(function ($query) use ($filter) {
                $provinceIds = SearchService::encodeStringToArray($filter['data']);

                foreach ($provinceIds as $id) {
                    $query->orWhere('stores.province_id', $id);
                }
            });
        }

        if ($filter['key'] == 'province_city_ids') {
            $query->where(function ($query) use ($filter) {
                $provinceCityIds = SearchService::encodeStringToArray($filter['data']);

                foreach ($provinceCityIds as $id) {
                    $query->orWhere('stores.province_city_id', $id);
                }
            });
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
            ->join('users', 'stores.user_id', '=', 'users.id')
            ->with([
                'province',
                'province.provinceDistrict',
                'provinceCity'
            ])
            ->selectRaw($this->getSelectRaw())
            ->orderByDesc('stores.created_at');
    }

    /**
     * select store
     * @return string
     */
    protected function getSelectRaw()
    {
        return 'stores.id,
            stores.name,
            stores.tel,
            stores.application_tel,
            stores.province_id,
            stores.province_city_id,
            stores.recruiter_name,
            stores.postal_code,
            stores.building,
            stores.address,
            stores.specialize_ids,
            users.last_name,
            users.first_name';
    }
}
