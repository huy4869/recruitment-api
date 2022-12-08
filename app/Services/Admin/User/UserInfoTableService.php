<?php

namespace App\Services\Admin\User;

use App\Exceptions\InputException;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\User;
use App\Services\Common\CommonService;
use App\Services\Common\SearchService;
use App\Services\TableService;

class UserInfoTableService extends TableService
{
    /**
     * @var string[]
     */
    protected $filterables = [
        'province_ids' => 'filterTypes',
        'province_city_ids' => 'filterTypes',
        'work_type_ids' => 'filterTypes',
        'job_type_ids' => 'filterTypes',
        'job_experience_ids' => 'filterTypes',
        'age' => 'filterTypes',
        'salary_type_id' => 'filterTypes',
        'salary_min' => 'filterTypes',
        'salary_max' => 'filterTypes',
    ];

    /**
     * @param $query
     * @param $filter
     * @return mixed
     * @throws InputException
     */
    protected function filterTypes($query, $filter)
    {
        if (!count($filter)) {
            return $query;
        }

        $query->whereHas('desiredConditionUser', function ($query) use ($filter) {
            $jsonKey = [
                'work_type_ids' => 'work_type_ids',
                'job_type_ids' => 'job_type_ids',
                'job_experience_ids' => 'job_experience_ids',
            ];

            if (!isset($filter['key']) || !isset($filter['data'])) {
                throw new InputException(trans('response.invalid'));
            }

            if (isset($jsonKey[$filterItem['key']])) {
                SearchService::queryJsonKey($query, $filter);
            } elseif ($filter['key'] == 'province_ids') {
                $query->where(function ($query) use ($filter) {
                    $provinceIds = json_decode($filter['data']);

                    foreach ($provinceIds as $id) {
                        $query->orWhere('province_id', $id);
                    }
                });
            } elseif ($filter['key'] == 'province_city_ids') {
                $query->where(function ($query) use ($filter) {
                    $provinceCityIds = json_decode($filter['data']);

                    foreach ($provinceCityIds as $id) {
                        $query->orWhere('province_city_id', $id);
                    }
                });
            } elseif ($filter['key'] == 'salary_min' || $filter['key'] == 'salary_max') {
                SearchService::queryRangeKey($query, $filter);
            } elseif ($filter['key'] == 'age') {
                $query->where('age', '>=', $filter['data']);
            } else {
                $query->where($filter['key'], $filter['data']);
            }//end if
        });

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function makeNewQuery()
    {
        return User::query()->roleUser()
            ->with([
                'avatarBanner',
                'province',
                'provinceCity',
                'desiredConditionUser.salaryType',
                'desiredConditionUser.province',
            ])
            ->selectRaw($this->getSelectRaw())
            ->orderByDesc('created_at');
    }

    /**
     * Get Select Raw
     *
     * @return string
     */
    protected function getSelectRaw()
    {
        return 'users.id,
            users.first_name,
            users.last_name,
            users.furi_first_name,
            users.furi_last_name,
            users.alias_name,
            users.province_id,
            users.province_city_id,
            users.tel,
            users.email,
            users.last_login_at,
            users.address,
            users.building,
            users.created_at';
    }
}
