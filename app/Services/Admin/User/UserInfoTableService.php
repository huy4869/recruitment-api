<?php

namespace App\Services\Admin\User;

use App\Exceptions\InputException;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\User;
use App\Services\Common\CommonService;
use App\Services\TableService;

class UserInfoTableService extends TableService
{
    const FIRST_ARRAY = 0;

    /**
     * @var string[]
     */
    protected $filterables = [
        'province_id' => 'filterTypes',
        'work_type_id' => 'filterTypes',
        'job_type_ids' => 'filterTypes',
        'job_experience_id' => 'filterTypes',
        'age' => 'filterTypes',
        'salary_type_id' => 'filterTypes',
        'salary_min' => 'filterTypes',
        'salary_max' => 'filterTypes',
    ];

    /**
     * @param $query
     * @param $filter
     * @param $filters
     * @return mixed
     * @throws InputException
     */
    protected function filterTypes($query, $filter, $filters)
    {
        if (!count($filters)) {
            return $query;
        }

        $query->whereHas('desiredConditionUser', function ($query) use ($filters) {

            foreach ($filters as $filterItem) {
                if (!isset($filterItem['key']) || !isset($filterItem['data'])) {
                    throw new InputException(trans('response.invalid'));
                }

                if ($filterItem['key'] == 'job_type_ids') {
                    $query->where(function ($query) use ($filterItem) {
                        $types = json_decode($filterItem['data']);

                        if ($types) {
                            foreach ($types as $type) {
                                $query->orWhereJsonContains($filterItem['key'], $type);

                                if ($filterItem['key'] == 'job_type_ids' && $type == MJobType::OTHER) {
                                    $otherJobTypeIds = CommonService::getOtherTypeIds(MJobType::getTableName());

                                    //other job types query
                                    foreach ($otherJobTypeIds as $jobType) {
                                        $query->orWhereJsonContains('job_type_ids', $jobType);
                                    }
                                }
                            }
                        }
                    });
                } elseif ($filterItem['key'] == 'work_type_id') {
                    if ($filterItem['data'] != MWorkType::OTHER) {
                        $int = (integer)$filterItem['data'];
                        $query->whereJsonContains('work_type_ids', $int);
                    } else {
                        $query->where(function ($query) {
                            $otherWorkType = CommonService::getOtherTypeIds(MWorkType::getTableName());
                            $query->whereJsonContains('work_type_ids', $otherWorkType[self::FIRST_ARRAY]);
                            unset($otherWorkType[self::FIRST_ARRAY]);

                            foreach ($otherWorkType as $workType) {
                                $query->orWhereJsonContains('work_type_ids', $workType);
                            }
                        });
                    }
                } elseif ($filterItem['key'] == 'job_experience_id') {
                    $int = (integer)$filterItem['data'];
                    $query->whereJsonContains('job_experience_ids', $int);
                } elseif ($filterItem['key'] == 'salary_min') {
                    $query->where('salary_min', '>=', $filterItem['data']);
                } elseif ($filterItem['key'] == 'salary_max') {
                    $query->where('salary_max', '<=', $filterItem['data']);
                } else {
                    $query->where($filterItem['key'], $filterItem['data']);
                }//end if
            }//end foreach
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
            users.age,
            users.tel,
            users.email,
            users.last_login_at,
            users.address,
            users.building,
            users.created_at';
    }
}
