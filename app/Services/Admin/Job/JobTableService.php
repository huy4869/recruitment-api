<?php

namespace App\Services\Admin\Job;

use App\Exceptions\InputException;
use App\Models\JobPosting;
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

        $rangeKey = [
            'salary_min',
            'salary_max',
            'age_min',
            'age_max',
        ];

        foreach ($filters as $filterItem) {
            if (!isset($filterItem['key']) || !isset($filterItem['data'])) {
                throw new InputException(trans('response.invalid'));
            }

            if (in_array($filterItem['key'], $jsonKey)) {
                $query->where(function ($query) use ($filterItem) {
                    $types = json_decode($filterItem['data']);

                    if ($types) {
                        $query->whereJsonContains($filterItem['key'], $types[self::FIRST_ARRAY]);
                        unset($types[self::FIRST_ARRAY]);

                        foreach ($types as $type) {
                            $query->orWhereJsonContains($filterItem['key'], $type);
                        }
                    }
                });
            } elseif (in_array($filterItem['key'], $rangeKey)) {
                preg_match('/([^_]+)_(min|max)/', $filterItem['key'], $matches);
                $keyMin = $matches[1] . '_min';
                $keyMax = $matches[1] . '_max';

                $query->where( function ($query) use ($keyMin, $filterItem) {
                    $query->whereNull($keyMin)
                        ->orWhere( function ($query) use ($keyMin, $filterItem) {
                            $query->whereNotNull($keyMin)
                                ->where($keyMin, '<=', $filterItem['data']);
                        });
                })
                ->where( function ($query) use ($keyMax, $filterItem) {
                    $query->whereNull($keyMax)
                        ->orWhere( function ($query) use ($keyMax, $filterItem) {
                            $query->whereNotNull($keyMax)
                                ->where($keyMax, '>=', $filterItem['data']);
                        });
                });
            } else {
                $query->where($filterItem['key'], $filterItem['data']);
            }
        }//end foreach

        return $query;
    }

    /**
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function makeNewQuery()
    {
        return JobPosting::query()->with([
            'store',
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
            job_postings.updated_at';
    }
}
