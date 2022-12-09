<?php

namespace App\Services\Common;

use App\Models\MJobType;
use App\Models\MWorkType;
use App\Services\Service;
use App\Services\User\Job\JobService;
use function JmesPath\search;

class SearchService extends Service
{
    const FIRST_ARRAY = 0;

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    public static function queryJsonKey($query, $filter)
    {
        $types = self::encodeStringToArray($filter['data']);

        return $query->where(function ($query) use ($filter, $types) {
            $query->whereJsonContains($filter['key'], $types[self::FIRST_ARRAY]);
            unset($types[self::FIRST_ARRAY]);

            foreach ($types as $type) {
                $query->orWhereJsonContains($filter['key'], $type);

                if ($filter['key'] == 'job_type_ids' && $type == MJobType::OTHER) {
                    $otherJobTypeIds = JobService::getOtherJobTypeIds();

                    //other job types query
                    foreach ($otherJobTypeIds as $jobType) {
                        $query->orWhereJsonContains('job_type_ids', $jobType);
                    }
                }

                if ($filter['key'] == 'work_type_ids' && $type == MWorkType::OTHER) {
                    $otherWorkTypeIds = JobService::getOtherWorkTypeIds();

                    //other work types query
                    foreach ($otherWorkTypeIds as $workType) {
                        $query->orWhereJsonContains('work_type_ids', $workType);
                    }
                }
            }//end foreach
        });
    }

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    public static function queryRangeKey($query, $filter)
    {
        preg_match('/([^_]+)_(min|max)/', $filter['key'], $matches);
        $keyMin = $matches[1] . '_min';
        $keyMax = $matches[1] . '_max';

        return $query->where( function ($query) use ($keyMin, $filter) {
            $query->whereNull($keyMin)
                ->orWhere( function ($query) use ($keyMin, $filter) {
                    $query->whereNotNull($keyMin)
                        ->where($keyMin, '<=', $filter['data']);
                });
        })
        ->where( function ($query) use ($keyMax, $filter) {
            $query->whereNull($keyMax)
                ->orWhere( function ($query) use ($keyMax, $filter) {
                    $query->whereNotNull($keyMax)
                        ->where($keyMax, '>=', $filter['data']);
                });
        });
    }

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    public static function queryJobProvinceKey($query, $filter)
    {
        $types = self::encodeStringToArray($filter['data']);
        $query->where('job_postings.' . $filter['key'], $types[self::FIRST_ARRAY]);
        unset($types[self::FIRST_ARRAY]);

        foreach ($types as $type) {
            $query->orWhere('job_postings.' . $filter['key'], $type);
        }

        return $query;
    }

    public static function encodeStringToArray($data)
    {
        if (!str_contains($data, '[')) {
            $data = array_map('intval', explode(',', $data));
        } else {
            $data = json_decode($data);
        }

        return $data;
    }
}
