<?php


namespace App\Services\Recruiter\User;

use App\Exceptions\InputException;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\User;
use App\Services\Common\SearchService;
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
                case 'job_feature_ids':
                    SearchService::queryJsonKey($query, $filter);
                    break;
                case 'salary_min':
                case 'salary_max':
                    SearchService::queryRangeKey($query, $filter);
                    break;
                case 'province_id':
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
                    $ageValues = config('user.age');

                    if (isset($ageValues[$filterItem['data']])) {
                        $query->where('age', '>=', $ageValues[$filter['data']]);
                        break;
                    }

                    break;
                default:
                    $query->where($filter['key'], $filter['data']);
            }
        });

        return $query;
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
