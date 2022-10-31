<?php

namespace App\Services\User\Job;

use App\Exceptions\InputException;
use App\Models\JobPosting;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Services\TableService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class JobTableService extends TableService
{
    const FIRST_ARRAY = 0;

    /**
     * @var array
     */
    protected $searchables = [
        'job_postings.name',
    ];

    /**
     * @var string[]
     */
    protected $filterables = [
        'job_type_ids' => 'filterTypes',
        'work_type_ids' => 'filterTypes',
        'experience_ids' => 'filterTypes',
        'feature_ids' => 'filterTypes',
        'province_id' => 'filterProvinces',
        'province_city_id' => 'filterProvinces',
        'list_type' => 'filterListType'
    ];

    /**
     * @var string[]
     */
    protected $orderables = [
        'created_at' => 'job_postings.created_at',
        'updated_at' => 'job_postings.updated_at'
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

        $jsonKey = [
            'work_type_ids',
            'job_type_ids',
            'experience_ids',
            'feature_ids',
        ];

        foreach ($filters as $filterItem) {
            if (!isset($filterItem['key']) || !isset($filterItem['data'])) {
                throw new InputException(trans('response.invalid'));
            }

            if (in_array($filterItem['key'], $jsonKey)) {
                $query->where(function ($query) use ($filterItem) {
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
            }//end if
        }//end foreach

        return $query;
    }

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    protected function filterProvinces($query, $filter)
    {
        if (!count($filter)) {
            return $query;
        }

        $provinceIds = json_decode($filter['data']);

        return $query->whereIn($filter['key'], $provinceIds);
    }

    protected function filterListType($query, $filter)
    {
        $mode = $filter['data'];

        switch ($mode) {
            case 'new':
                return $query->new();
            case 'most_favorite':
                return $query->select('job_postings.*', DB::raw('COUNT(favorite_jobs.id) as total_favorites'))
                    ->join('favorite_jobs', 'job_postings.id', '=', 'favorite_jobs.job_posting_id')
                    ->groupBy('job_postings.id')
                    ->orderBy('total_favorites', 'desc');
            case 'most_view':
                return $query->orderBy('views', 'desc');
            case 'recommend':
                return $query->select('job_postings.*', 'suitability_point')
                    ->join('user_job_desired_matches', 'job_postings.id', '=', 'user_job_desired_matches.job_id')
                    ->where('user_job_desired_matches.user_id', $this->user->id)
                    ->orderBy('suitability_point', 'desc');
            default:
                return $query;
        }
    }

    /**
     * @return Builder
     */
    public function makeNewQuery()
    {
        return JobPosting::query()->released()
            ->with(
                'store',
                'salaryType',
                'provinceCity',
                'province',
                'province.provinceDistrict',
                'bannerImage',
            )
            ->selectRaw($this->getSelectRaw());
    }

    /**
     * Get Select Raw
     *
     * @return string
     */
    protected function getSelectRaw()
    {
        return 'job_postings.id,
            job_postings.store_id,
            job_postings.job_type_ids,
            job_postings.work_type_ids,
            job_postings.job_status_id,
            job_postings.postal_code,
            job_postings.province_id,
            job_postings.province_city_id,
            job_postings.city,
            job_postings.address,
            job_postings.name,
            job_postings.views,
            job_postings.description,
            job_postings.salary_min,
            job_postings.salary_max,
            job_postings.salary_type_id,
            job_postings.start_work_time,
            job_postings.end_work_time,
            job_postings.feature_ids,
            job_postings.experience_ids,
            job_postings.created_at,
            job_postings.updated_at,
            job_postings.released_at';
    }
}
