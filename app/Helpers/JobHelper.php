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
     * @param $favoriteJob
     * @param $masterData
     * @return array
     */
    public static function addFormatFavoriteJsonData($favoriteJob, $masterData)
    {
        $jobPosting = $favoriteJob->jobPosting;
        $workTypes = self::getTypeName($jobPosting->work_type_ids, $masterData['masterWorkTypes']);
        $jobTypes = self::getTypeName($jobPosting->job_type_ids, $masterData['masterJobTypes']);

        return [
            'favoriteId' => $favoriteJob->id,
            'job_name' => $jobPosting->name,
            'store_name' => $jobPosting->store->name,
            'interview_status' => $jobPosting->applications->first()->interviews->name ?? null,
            'postal_code' => $jobPosting->postal_code,
            'province' => $jobPosting->province->name,
            'city' => $jobPosting->city,
            'address' => $jobPosting->address,
            'salary_min' => $jobPosting->salary_min,
            'salary_max' => $jobPosting->salary_max,
            'salary_type' => $jobPosting->salaryType->name,
            'start_work_time' => $jobPosting->start_work_time,
            'end_work_time' => $jobPosting->end_work_time,
            'holiday_description' => $jobPosting->holiday_description,
            'description' => $jobPosting->description,
            'banner_image' => FileHelper::getFullUrl($jobPosting->bannerImage->url ?? null),
            'work_types' => $workTypes,
            'job_types' => $jobTypes,
        ];
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
            $result[] = $masterDataType[(int)$id - 1];
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
