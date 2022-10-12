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
        $masterGenders = JobService::getMasterDataJobGenders();
        $userFavoriteJobs = JobService::getUserFavoriteJobIds($user);
        $userApplyJobs = JobService::getUserApplyJobIds($user);

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'masterGenders' => $masterGenders,
            'userFavoriteJobs' => $userFavoriteJobs,
            'userApplyJobs' => $userApplyJobs,
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
        $gender = self::getTypeName($job->gender_ids, $masterData['masterGenders']);

        $isFavorite = self::inArrayCheck($job->id, $masterData['userFavoriteJobs']);
        $isApply = self::inArrayCheck($job->id, $masterData['userApplyJobs']);

        return array_merge($job->toArray(), [
            'store_name' => $job->store->name,
            'banner_image' => FileHelper::getFullUrl($job->bannerImage->url ?? null),
            'detail_images' => $job->detailImages,
            'province' => $job->province->name,
            'district' => $job->province->provinceDistrict->name,
            'salary_type' => $job->salaryType->name,
            'work_types' => $workTypes,
            'job_types' => $jobTypes,
            'genders' => $gender,
            'is_favorite' => $isFavorite,
            'is_apply' => $isApply,
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

        if (!$typeIds) {
            return $result;
        }

        foreach ($typeIds as $id) {
            $result[] = $masterDataType[$id - 1];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public static function inArrayCheck($id, $array)
    {
        if (!$array) {
            return false;
        }

        return in_array($id, $array);
    }
}
