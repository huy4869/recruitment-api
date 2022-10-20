<?php

namespace App\Helpers;

use App\Services\User\Job\JobService;

class UserHelper
{
    public static function getPercentage($data, $percentage)
    {
        foreach ($data as $value) {
            if (is_null($value)) {
                $percentage = config('percentage.default');
                break;
            }
        }

        return $percentage;
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
}
