<?php

namespace App\Services\Admin\User;

use App\Exceptions\InputException;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\User;
use App\Services\Common\CommonService;
use App\Services\Common\SearchService;
use App\Services\TableService;
use Doctrine\DBAL\Exception;

class UserInfoTableService extends TableService
{
    const FIRST_ARRAY = 0;

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
            if (!isset($filter['key']) || !isset($filter['data'])) {
                throw new InputException(trans('response.invalid'));
            }

            switch ($filter['key']) {
                case 'work_type_ids':
                case 'job_type_ids':
                case 'job_experience_ids':
                    SearchService::queryJsonKey($query, $filter);
                    break;
                case 'salary_min':
                case 'salary_max':
                    SearchService::queryRangeKey($query, $filter);
                    break;
                case 'province_ids':
                    $query->where(function ($query) use ($filter) {
                        $types = json_decode($filter['data']);
                        $query->whereJsonContains('province_ids', $types[self::FIRST_ARRAY]);
                        unset($types[self::FIRST_ARRAY]);

                        foreach ($types as $type) {
                            $query->orWhereJsonContains('province_ids', $type);
                        }//end foreach
                    });

                    break;
                case 'age':
                    $query->where('age', '>=', $filter['data']);
                    break;
                default:
                    $query->where($filter['key'], $filter['data']);
            }//end switch
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
