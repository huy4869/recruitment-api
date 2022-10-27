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
        $percentPeriod = config('percentage.default');

        foreach ($workHistories as $workHistory) {
            if ($workHistory->period_start && $workHistory->period_end) {
                $percentPeriod += config('percentage.complete');
            } else {
                $percentPeriod += config('percentage.default');
            }
        }

        if ($percentPeriod > config('percentage.default')) {
            $percentPeriod = config('percentage.complete');
        }

        $percentStoreName = self::getPercentage($workHistories, 'store_name', config('percentage.complete'));
        $percentJobType = self::getPercentage($workHistories, 'job_type_id', config('percentage.complete'));
        $percentPositionOffice = self::getPercentage($workHistories, 'position_office_ids', config('percentage.work_history'));
        $percentBusinessContent = self::getPercentage($workHistories, 'business_content', config('percentage.work_history'));
        $percentExperienceAccumulation = self::getPercentage($workHistories, 'experience_accumulation', config('percentage.work_history'));

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

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'masterJobExperiences' => $masterJobExperiences,
            'masterJobFeatures' => $masterJobFeatures,
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
}
