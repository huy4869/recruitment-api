<?php

namespace App\Services\Recruiter\Job;

use App\Exceptions\InputException;
use App\Models\JobPosting;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Services\TableService;
use App\Services\User\Job\JobService;

class JobTableService extends TableService
{
    const FIRST_ARRAY = 0;

    /**
     * @var array
     */
    protected $searchables = [
    ];

    /**
     * @var string[]
     */
    protected $filterables = [
        'province_id' => 'filterTypes',
        'province_city_id' => 'filterTypes',
        'job_type_ids' => 'filterTypes',
        'work_type_ids' => 'filterTypes',
        'job_status_id' => 'filterTypes',
        'age_min' => 'filterTypes',
        'age_max' => 'filterTypes',
        'experience_ids' => 'filterTypes',
        'salary_type_id' => 'filterTypes',
        'salary_min' => 'filterTypes',
        'salary_max' => 'filterTypes',
        'gender_ids' => 'filterTypes',
        'job_name' => 'filterJobName',
        'store_name' => 'filterStoreName',
        'store_id' => 'filterTypes',
    ];

    /**
     * @var string[]
     */
    protected $orderables = [
        'updated_at' => 'job_postings.updated_at'
    ];

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    protected function filterJobName($query, $filter)
    {
        return $query->where('job_postings.name', 'like', '%' . $filter['data'] . '%');
    }

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    protected function filterStoreName($query, $filter)
    {
        return $query->where('stores.name', 'like', '%' . $filter['data'] . '%');
    }

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
            'gender_ids',
        ];

        $rangeMinKey = [
            'salary_min',
            'age_min',
        ];

        $rangeMaxKey = [
            'salary_max',
            'age_max',
        ];

        $provinceKey = [
            'province_id',
            'province_city_id',
        ];

        $skipKey = [
            'store_name',
            'job_name',
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
            } elseif (in_array($filterItem['key'], $rangeMinKey)) {
                $query->where($filterItem['key'], '>=', $filterItem['data']);
            } elseif (in_array($filterItem['key'], $rangeMaxKey)) {
                $query->where($filterItem['key'], '<=', $filterItem['data']);
            } elseif (in_array($filterItem['key'], $provinceKey)) {
                $types = json_decode($filterItem['data']);
                $query->where('job_postings.' . $filterItem['key'], $types[self::FIRST_ARRAY]);
                unset($types[self::FIRST_ARRAY]);

                foreach ($types as $type) {
                    $query->orWhere('job_postings.' . $filterItem['key'], $type);
                }
            } else {
                if (!in_array($filterItem['key'], $skipKey)) {
                    $query->where($filterItem['key'], $filterItem['data']);
                }
            }//end if
        }//end foreach

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function makeNewQuery()
    {
        $recruiter = $this->user;
        $recruiterStoreIds = $recruiter->stores->pluck('id')->toArray();

        return JobPosting::query()->whereIn('store_id', $recruiterStoreIds)
            ->join('stores', 'store_id', '=', 'stores.id')
            ->with([
                'store',
                'status',
                'province',
                'province.provinceDistrict',
                'salaryType',
                'bannerImage',
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
        return 'job_postings.id,
            job_postings.store_id,
            job_postings.job_type_ids,
            job_postings.work_type_ids,
            job_postings.job_status_id,
            job_postings.postal_code,
            job_postings.province_id,
            job_postings.province_city_id,
            job_postings.building,
            job_postings.address,
            job_postings.name,
            job_postings.description,
            job_postings.salary_min,
            job_postings.salary_max,
            job_postings.salary_type_id,
            job_postings.start_work_time,
            job_postings.end_work_time,
            job_postings.gender_ids,
            job_postings.feature_ids,
            job_postings.experience_ids,
            job_postings.created_at,
            job_postings.updated_at,
            job_postings.released_at,
            stores.name as store_name';
    }
}
