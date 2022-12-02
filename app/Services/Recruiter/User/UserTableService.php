<?php


namespace App\Services\Recruiter\User;

use App\Exceptions\InputException;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\User;
use App\Services\TableService;
use App\Services\User\Job\JobService;
use Illuminate\Support\Facades\DB;

class UserTableService extends TableService
{
    const FIRST_ARRAY = 0;

    /**
     * @var string[]
     */
    protected $filterables = [
        'work_type_ids' => 'filterTypes',
        'job_type_ids' => 'filterTypes',
        'job_experience_ids' => 'filterTypes',
        'job_feature_ids' => 'filterTypes',
        'age' => 'filterTypes',
        'salary_type_id' => 'filterTypes',
        'salary_min' => 'filterTypes',
        'salary_max' => 'filterTypes',
        'province_id' => 'filterTypes',
        'list_type' => 'filterListType'
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

                switch ($filterItem['key']) {
                    case 'work_type_ids':
                    case 'job_type_ids':
                    case 'job_experience_ids':
                    case 'job_feature_ids':
                        self::filterJsonTypes($query, $filterItem);
                        break;
                    case 'salary_min':
                        $query->where('salary_min', '>=', $filterItem['data']);
                        break;
                    case 'salary_max':
                        $query->where('salary_max', '<=', $filterItem['data']);
                        break;
                    case 'province_id':
                        $query->where(function ($query) use ($filterItem) {
                            $types = json_decode($filterItem['data']);
                            $query->whereJsonContains('province_ids', $types[self::FIRST_ARRAY]);
                            unset($types[self::FIRST_ARRAY]);

                            foreach ($types as $type) {
                                $query->orWhereJsonContains('province_ids', $type);
                            }//end foreach
                        });

                        break;
                    case 'age':
                        $ageValues = config('user.age');

                        if (isset($ageValues[$filterItem['data']])) {
                            $query->where('age', '>=', $ageValues[$filterItem['data']]);
                            break;
                        }

                        break;
                    default:
                        $query->where($filterItem['key'], $filterItem['data']);
                }
            }
        });

        return $query;
    }

    protected function filterJsonTypes($query, $filterItem)
    {
        return $query->where(function ($query) use ($filterItem) {
            $types = json_decode($filterItem['data']);
            $query->whereJsonContains($filterItem['key'], $types[self::FIRST_ARRAY]);
            unset($types[self::FIRST_ARRAY]);

            foreach ($types as $type) {
                $query->orWhereJsonContains($filterItem['key'], $type);

                if ($filterItem['key'] == 'job_type_ids' && $type == MJobType::OTHER) {
                    $otherJobTypeIds = JobService::getOtherJobTypeIds();

                    //other job types query
                    foreach ($otherJobTypeIds as $jobType) {
                        $query->orWhereJsonContains('job_type_ids', $jobType);
                    }
                }

                if ($filterItem['key'] == 'work_type_ids' && $type == MWorkType::OTHER) {
                    $otherWorkTypeIds = JobService::getOtherWorkTypeIds();

                    //other work types query
                    foreach ($otherWorkTypeIds as $workType) {
                        $query->orWhereJsonContains('work_type_ids', $workType);
                    }
                }
            }//end foreach
        });
    }

    protected function filterListType($query, $filter)
    {
        if (isset($filter['data'])) {
            $mode = $filter['data'];

            if ($mode == 'recommend_users') {
                $jobOwnedIds = auth()->user()->jobsOwned()->pluck('job_postings.id')->toArray();

                $query->select('users.*', DB::raw('sum(suitability_point) as point'))
                    ->leftJoin('user_job_desired_matches', 'users.id', '=', 'user_id')
                    ->whereIn('user_job_desired_matches.job_id', $jobOwnedIds)
                    ->groupBy('user_id')
                    ->orderBy('point', 'DESC')
                    ->orderBy('last_login_at', 'DESC');
            }
        }

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
                'province.provinceDistrict',
                'desiredConditionUser.salaryType',
                'desiredConditionUser.province',
                'desiredConditionUser.province.provinceDistrict',
            ])
            ->selectRaw($this->getSelectRaw());
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
            users.birthday,
            users.tel,
            users.email,
            users.last_login_at,
            users.created_at';
    }
}
