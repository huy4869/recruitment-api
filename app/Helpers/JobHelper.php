<?php

namespace App\Helpers;

use App\Models\Gender;
use App\Models\MJobExperience;
use App\Models\MJobFeature;
use App\Models\MJobType;
use App\Models\MStation;
use App\Models\MWorkType;
use App\Services\Common\CommonService;
use App\Services\User\Job\JobService;
use Carbon\Carbon;

class JobHelper
{
    /**
     * @return array
     */
    public static function getJobMasterData($needMasterData = [])
    {
        $masterData = [];

        if (!count($needMasterData)) {
            $needMasterData = [
                MJobType::getTableName(),
                MWorkType::getTableName(),
                MJobExperience::getTableName(),
                MJobFeature::getTableName(),
                Gender::getTableName(),
                MStation::getTableName(),
            ];
        }

        foreach ($needMasterData as $table) {
            $masterData[$table] = CommonService::getMasterDataFromTable($table);
        }

        return [
            'masterWorkTypes' => $masterData['m_work_types'] ?? [],
            'masterJobTypes' => $masterData['m_job_types'] ?? [],
            'masterJobExperiences' => $masterData['m_job_experiences'] ?? [],
            'masterJobFeatures' => $masterData['m_job_features'] ?? [],
            'masterGenders' => $masterData['m_genders'] ?? [],
            'masterStations' => $masterData['m_stations'] ?? [],
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
            'banner_image' => FileHelper::getFullUrl($job->bannerImage->url ?? null),
            'detail_images' => $job->detailImages,
            'province' => @$job->province->name,
            'province_city' => @$job->provinceCity->name,
            'salary_type' => @$job->salaryType->name,
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
     * @param $typeIds
     * @param $masterDataType
     * @return array
     */
    public static function getTypeName($typeIds, $masterDataType)
    {
        $result = [];
        $data = [];
        if (!$typeIds || !$masterDataType) {
            return $result;
        }

        foreach ($masterDataType as $value) {
            $data[$value['id']] = $value['name'];
        }

        foreach ($typeIds as $id) {
            $result[] = [
                'id' => (int)$id,
                'name' => $data[$id]
            ];
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
        $dataFeature = [];
        $categories = [];

        if (!$typeIds || !$features) {
            return $result;
        }

        foreach ($features as $feature) {
            $dataFeature[$feature['id']] = [
                'category_id' => $feature['category_id'],
                'category_name' => $feature['category']['name'],
                'name' => $feature['name'],
            ];
        }

        foreach ($typeIds as $id) {
            $categories[] = [
                'category_id' => $dataFeature[$id]['category_id'],
                'category_name' => $dataFeature[$id]['category_name'],
            ];
        }

        $categories = collect($categories)->unique('category_id')->toArray();

        foreach ($categories as $category) {
            foreach ($typeIds as $id) {
                $category['features'][] = $dataFeature[$id]['name'];
            }

            $category['features'] = implode('/', $category['features']);
            $result[] = $category;
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
        $data = [];

        if (!$typeIds || !$stations) {
            return $result;
        }

        foreach ($stations as $station) {
            $data[$station['id']] = [
                'province_name' => $station['province_name'],
                'railway_name' => $station['railway_name'],
                'station_name' => $station['station_name'],
            ];
        }

        foreach ($typeIds as $id) {
            $result[] = [
                'id' => (int)$id,
                'province_name' => $data[$id]['province_name'],
                'railway_name' => $data[$id]['railway_name'],
                'station_name' => $data[$id]['station_name'],
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

    /**
     * @param $date
     * @return bool
     */
    public static function isNew($date)
    {
        if (!$date) {
            return false;
        }

        $twoWeekAgo = Carbon::now()->subDays(config('validate.date_range.new_job_marker'));

        return $date >= $twoWeekAgo;
    }

    /**
     * @param $charTime
     * @return string
     */
    public static function makeWorkTimeFormat($charTime)
    {
        $startWorkingHours = substr($charTime, 0, 2);
        $startWorkingMinutes = substr($charTime, 2);

        return sprintf('%s:%s', $startWorkingHours, $startWorkingMinutes);
    }

    /**
     * @param $number
     * @return string
     */
    public static function thousandNumberFormat($number)
    {
        return number_format($number, 0, '.', ',');
    }

    public static function getWorkingDays($dayIds, $masterDataDays)
    {
        $result = [];

        if (!$dayIds || !$masterDataDays) {
            return $result;
        }

        foreach ($dayIds as $id) {
            $result[] = [
                'id' => (int)$id,
                'name' => $masterDataDays[$id],
            ];
        }

        return $result;
    }
}
