<?php

namespace App\Helpers;

use App\Services\User\Job\JobService;

class JobHelper
{
    /**
     * @return array
     */
    public static function getJobMasterData()
    {
        $masterWorkTypes = JobService::getMasterDataJobPostingWorkTypes();
        $masterJobTypes = JobService::getMasterDataJobPostingTypes();
        $masterGenders = JobService::getMasterDataJobGenders();
        $masterJobExperiences = JobService::getMasterDataJobExperiences();
        $masterJobFeatures = JobService::getMasterDataJobFeatures();
        $masterStations = JobService::getMasterDataStations();

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'masterJobExperiences' => $masterJobExperiences,
            'masterJobFeatures' => $masterJobFeatures,
            'masterGenders' => $masterGenders,
            'masterStations' => $masterStations,
        ];
    }

    /**
     * @param $masterData
     * @return array
     */
    public static function getListIdsMasterData($masterData)
    {
        $masterWorkTypeIds = self::pluckIdMasterData($masterData['masterWorkTypes']);
        $masterJobTypeIds = self::pluckIdMasterData($masterData['masterJobTypes']);
        $masterGenderIds = self::pluckIdMasterData($masterData['masterGenders']);
        $masterJobExperienceIds = self::pluckIdMasterData($masterData['masterJobFeatures']);
        $masterJobFeatureIds = self::pluckIdMasterData($masterData['masterJobFeatures']);
        $masterStations = self::pluckIdMasterData($masterData['masterStations']);

        return [
            'masterWorkTypes' => $masterWorkTypeIds,
            'masterJobTypes' => $masterJobTypeIds,
            'masterGenders' => $masterGenderIds,
            'masterJobExperiences' => $masterJobExperienceIds,
            'masterJobFeatures' => $masterJobFeatureIds,
            'masterStations' => $masterStations,
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public static function pluckIdMasterData($data)
    {
        return collect($data)->pluck('id')->toArray();
    }

    /**
     * @param $user
     * @return array
     */
    public static function getUserActionJob($user)
    {
        $userFavoriteJobs = JobService::getUserFavoriteJobIds($user);
        $userApplyJobs = JobService::getUserApplyJobIds($user);

        return [
            'userFavoriteJobs' => $userFavoriteJobs,
            'userApplyJobs' => $userApplyJobs,
        ];
    }


    /**
     * Add format job json data
     *
     * @param $job
     * @param $masterData
     * @param $userAction
     * @return array
     */
    public static function addFormatJobJsonData($job, $masterData, $userAction)
    {
        $workTypes = self::getTypeName($job->work_type_ids, $masterData['masterWorkTypes']);
        $jobTypes = self::getTypeName($job->job_type_ids, $masterData['masterJobTypes']);
        $gender = self::getTypeName($job->gender_ids, $masterData['masterGenders']);
        $experience = self::getTypeName($job->experience_ids, $masterData['masterJobExperiences']);
        $feature = self::getFeatureCategoryName($job->feature_ids, $masterData['masterJobFeatures']);
        $stations = self::getStations($job->station_ids, $masterData['masterStations']);

        $isFavorite = self::inArrayCheck($job->id, $userAction['userFavoriteJobs']);
        $isApply = self::inArrayCheck($job->id, $userAction['userApplyJobs']);

        return array_merge($job->toArray(), [
            'store_name' => $job->store->name,
            'banner_image' => FileHelper::getFullUrl($job->bannerImage->url ?? null),
            'detail_images' => $job->detailImages,
            'province' => $job->province->name,
            'district' => $job->province->provinceDistrict->name,
            'salary_type' => $job->salaryType->name,
            'experience_types' => $experience,
            'feature_types' => $feature,
            'work_types' => $workTypes,
            'job_types' => $jobTypes,
            'genders' => $gender,
            'is_favorite' => $isFavorite,
            'is_apply' => $isApply,
            'stations' => $stations,
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
     * @param $typeIds
     * @param $features
     * @return array
     */
    public static function getFeatureCategoryName($typeIds, $features)
    {
        $result = [];

        if (!$typeIds) {
            return $result;
        }

        foreach ($typeIds as $id) {
            $feature = $features[$id - 1];

            $result[] = [
                'category' => [
                    'id' => $feature['category']['id'],
                    'name' => $feature['category']['name'],
                    'feature' => [
                        'id' => $feature['id'],
                        'name' => $feature['name'],
                    ]
                ],
            ];
        }

        return $result;
    }

    /**
     * @param $typeIds
     * @param $provinces
     * @return array
     */
    public static function getProvinceDistrictName($typeIds, $provinces)
    {
        $result = [];

        if (!$typeIds) {
            return $result;
        }

        foreach ($typeIds as $id) {
            $province = $provinces[$id - 1];

            $result[] = [
                'district' => [
                    'id' => $province['province_district']['id'],
                    'name' => $province['province_district']['name'],
                    'province' => [
                        'id' => $province['id'],
                        'name' => $province['name'],
                    ]
                ],
            ];
        }

        return $result;
    }

    /**
     * @param $typeIds
     * @param $stations
     * @return array
     */
    public static function getStations($typeIds, $stations)
    {
        $result = [];

        if (!$typeIds) {
            return $result;
        }

        foreach ($typeIds as $id) {
            $station = $stations[$id - 1];

            $result[] = [
                'id' => $station['id'],
                'province_name' => $station['province_name'],
                'railway_name' => $station['railway_name'],
                'station_name' => $station['station_name'],
            ];
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
