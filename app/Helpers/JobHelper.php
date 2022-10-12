<?php

namespace App\Helpers;

use App\Services\User\Job\JobService;

class JobHelper
{
    /**
     * @return array
     */
    public static function getJobMasterData($user)
    {
        $masterWorkTypes = JobService::getMasterDataJobPostingWorkTypes();
        $masterJobTypes = JobService::getMasterDataJobPostingTypes();
        $userFavoriteJobs = JobService::getUserFavoriteJobIds($user);

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'userFavoriteJobs' => $userFavoriteJobs,
        ];
    }

    /**
     * Add format job json data
     *
     * @param $job
     * @param $masterData
     * @return array
     */
    public static function addFormatJobJsonData($job, $masterData)
    {
        $workTypes = self::getTypeName($job->work_type_ids, $masterData['masterWorkTypes']);
        $jobTypes = self::getTypeName($job->job_type_ids, $masterData['masterJobTypes']);

        if (!$masterData['userFavoriteJobs']) {
            $isFavorite = $masterData['userFavoriteJobs'];
        } else {
            $isFavorite = in_array($job->id, $masterData['userFavoriteJobs']);
        }

        return array_merge($job->toArray(), [
            'store_name' => $job->store->name,
            'province' => $job->province->name,
            'district' => $job->province->provinceDistrict->name,
            'salary_type' => $job->salaryType->name,
            'is_favorite' => $isFavorite,
            'work_types' => $workTypes,
            'job_types' => $jobTypes,
        ]);
    }

    /**
     * @param $typeIds
     * @param $masterDataType
     * @return array
     */
    public static function getTypeName($typeIds, $masterDataType)
    {
        $result = [];

        foreach ($typeIds as $id) {
            $result[] = $masterDataType[$id - 1];
        }

        return $result;
    }
}
