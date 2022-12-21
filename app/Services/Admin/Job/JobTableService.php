<?php

namespace App\Services\Admin\Job;

use App\Exceptions\InputException;
use App\Helpers\StringHelper;
use App\Models\JobPosting;
use App\Services\Common\SearchService;
use App\Services\TableService;
use Illuminate\Database\Eloquent\Builder;

class JobTableService extends TableService
{
    const FIRST_ARRAY = 0;

    /**
     * @var array
     */
    protected $searchables = [
        'name' => 'job_postings.name'
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
        $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);

        return $query->where('job_postings.name', 'like', '%' . $filter['data'] . '%');
    }

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    protected function filterStoreName($query, $filter)
    {
        $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);

        return $query->where('stores.name', 'like', '%' . $filter['data'] . '%');
    }

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

        $jsonKey = [
            'work_type_ids',
            'job_type_ids',
            'experience_ids',
            'gender_ids',
        ];

        $rangeKey = [
            'salary_min',
            'salary_max',
            'age_min',
            'age_max',
        ];

        $provinceKey = [
            'province_id',
            'province_city_id',
        ];

        if (!isset($filter['key']) || !isset($filter['data'])) {
            throw new InputException(trans('response.invalid'));
        }

        if (in_array($filter['key'], $jsonKey)) {
            SearchService::queryJsonKey($query, $filter);
        } elseif (in_array($filter['key'], $rangeKey)) {
            SearchService::queryRangeKey($query, $filter);
        } elseif (in_array($filter['key'], $provinceKey)) {
            SearchService::queryJobProvinceKey($query, $filter);
        } else {
            $query->where($filter['key'], $filter['data']);
        }

        return $query;
    }

    /**
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function makeNewQuery()
    {
        return JobPosting::query()
            ->join('stores', 'stores.id', '=', 'job_postings.store_id')
            ->with([
            'store',
            'store.owner',
            'status',
            'province',
            'provinceCity',
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
            job_postings.updated_at,
            job_postings.start_work_time_type,
            job_postings.end_work_time_type,
            job_postings.range_hours_type';
    }
}
