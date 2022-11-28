<?php

namespace App\Helpers;

use App\Services\Recruiter\UserProfileService;
use App\Services\User\Job\JobService;

class UserHelper
{
    /**
     * @param $data
     * @param $key
     * @param $percentage
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function getPercentage($data, $key, $percentage)
    {
        $array = $data->pluck($key)->toArray();
        foreach ($array as $value) {
            if ($value) {
                return $percentage;
            }
        }

        return config('percentage.default');
    }

    /**
     * @param $workHistories
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function getPercentWorkHistory($workHistories)
    {
        $percentStoreName = self::getPercentage($workHistories, 'store_name', config('percentage.work_history.attribute.store_name'));
        $percentJobType = self::getPercentage($workHistories, 'job_type_id', config('percentage.work_history.attribute.job_type_id'));
        $percentPositionOffice = self::getPercentage($workHistories, 'position_office_ids', config('percentage.work_history.attribute.position_office_ids'));
        $percentBusinessContent = self::getPercentage($workHistories, 'business_content', config('percentage.work_history.attribute.business_content'));
        $percentExperienceAccumulation = self::getPercentage($workHistories, 'experience_accumulation', config('percentage.work_history.attribute.experience_accumulation'));
        $percentPeriod = self::getPercentage($workHistories, 'period_start', config('percentage.work_history.attribute.period_start'));

        return $percentStoreName + $percentJobType + $percentPositionOffice + $percentBusinessContent + $percentPeriod + $percentExperienceAccumulation;
    }

    /**
     * @param $data
     * @return string|null
     */
    public static function getNewDate($data)
    {
        if ($data->isEmpty()) {
            return null;
        }

        $object = $data->sortByDesc('updated_at')->first();

        return $object->updated_at ? $object->updated_at->format('Y/m/d') : null;
    }

    /**
     * @return array
     */
    public static function getJobMasterData()
    {
        $masterWorkTypes = JobService::getMasterDataJobPostingWorkTypes();
        $masterJobTypes = JobService::getMasterDataJobPostingTypes();
        $masterJobExperiences = JobService::getMasterDataJobExperiences();
        $masterJobFeatures = JobService::getMasterDataJobFeatures();
        $masterProvinces = JobService::getMasterDataProvinces();

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'masterJobExperiences' => $masterJobExperiences,
            'masterJobFeatures' => $masterJobFeatures,
            'masterProvinces' => $masterProvinces,
        ];
    }

    /**
     * get master data masterWorkTypes masterJobTypes masterPositionOffice
     *
     * @return array
     */
    public static function getMasterDataWithUser()
    {
        $masterWorkTypes = JobService::getMasterDataJobPostingWorkTypes();
        $masterJobTypes = JobService::getMasterDataJobPostingTypes();
        $masterPositionOffice = UserProfileService::getMasterDataPositionOffice();

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'masterPositionOffice' => $masterPositionOffice,
        ];
    }

    public static function getSkillUser($skills)
    {
        $skillUser = [];
        $dataSkill = config('user.skill_types');

        foreach ($skills as $skill) {
            $skillUser[] = [
                'type' => $skill['type'],
                'name' => $dataSkill[$skill['type']]['name'],
                'level' => $skill['level'],
            ];
        }

        return $skillUser;
    }

    public static function getListProvinceNames($provinceIds, $masterData)
    {
        $provinces = JobHelper::getTypeName(
            $provinceIds,
            $masterData
        );

        foreach ($provinces as $province) {
            $provinceNameArray[] = $province['name'];
        }

        return implode('、', $provinceNameArray);
    }

    public static function getFeature($typeIds, $masterDataType)
    {
        $result = [];

        if (!$typeIds || !$masterDataType) {
            return $result;
        }

        foreach ($typeIds as $id) {
            $feature = $masterDataType[(int)$id - 1];

            $result[] = [
                'id' => $feature['id'],
                'name' => $feature['name'],
            ];
        }

        return $result;
    }
}
