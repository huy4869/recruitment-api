<?php

namespace App\Helpers;

use App\Services\User\Job\JobService;

class SearchJobHelper
{
    /**
     * @return array
     */
    public static function getJobMasterData()
    {
        $masterWorkTypes = JobService::getMasterDataJobPostingWorkTypes();
        $masterJobTypes = JobService::getMasterDataJobPostingTypes();
        $masterJobExps = JobService::getMasterDataJobExperiences();
        $masterJobFeatures = JobService::getMasterDataJobFeatures();
        $masterProvinces = JobService::getMasterDataProvinces();

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'masterJobExps' => $masterJobExps,
            'masterJobFeatures' => $masterJobFeatures,
            'masterProvinces' => $masterProvinces,
        ];
    }

    /**
     * Add format job json data
     *
     * @param $searchJob
     * @param $masterData
     * @return bool
     */
    public static function addFormatSearchJobJsonData($searchJob, $masterData)
    {
        $content = $searchJob->content;

        if (isset($content['work_type_ids'])) {
            $content['work_type_ids'] = JobHelper::getTypeName(
                $content['work_type_ids'],
                $masterData['masterWorkTypes']
            );
        }

        if (isset($content['job_type_ids'])) {
            $content['job_type_ids'] = JobHelper::getTypeName(
                $content['job_type_ids'],
                $masterData['masterJobTypes']
            );
        }

        if (isset($content['experience_ids'])) {
            $content['experience_ids'] = JobHelper::getTypeName(
                $content['experience_ids'],
                $masterData['masterJobExps']
            );
        }

        if (isset($content['feature_ids'])) {
            $content['feature_ids'] = JobHelper::getFeatureCategoryName(
                $content['feature_ids'],
                $masterData['masterJobFeatures'],
            );
        }

        if (isset($content['province_ids'])) {
            $content['province_ids'] = JobHelper::getProvinceDistrictName(
                $content['province_ids'],
                $masterData['masterProvinces'],
            );
        }

        if (isset($content['order_by_ids'])) {
            $content['order_by_ids'] = JobHelper::getTypeName(
                $content['order_by_ids'],
                config('order_by.job_posting')
            );
        }

        $searchJob->content = $content;

        return $searchJob;
    }
}
