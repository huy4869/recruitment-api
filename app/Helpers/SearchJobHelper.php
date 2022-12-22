<?php

namespace App\Helpers;

use App\Services\Common\CommonService;
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
        $masterProvinceCities = CommonService::getMasterDataProvinceCities();

        return [
            'masterWorkTypes' => $masterWorkTypes,
            'masterJobTypes' => $masterJobTypes,
            'masterJobExps' => $masterJobExps,
            'masterJobFeatures' => $masterJobFeatures,
            'masterProvinces' => $masterProvinces,
            'masterProvinceCities' => $masterProvinceCities,
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
            $content['feature_ids'] = JobHelper::getTypeName(
                $content['feature_ids'],
                $masterData['masterJobFeatures'],
            );
        }

        if (isset($content['province_id'])) {
            $content['province_id'] = JobHelper::getTypeName(
                $content['province_id'],
                $masterData['masterProvinces'],
            );
        }

        if (isset($content['province_city_id'])) {
            $content['province_city_id'] = self::getProvinceCityDistrictName(
                $content['province_city_id'],
                $masterData['masterProvinceCities'],
            );
        }

        if (isset($content['order_by_id'])) {
            $content['order_by_id'] = config('order_by.job_posting.' . $content['order_by_id']);
        }

        $searchJob->content = $content;

        return $searchJob;
    }

    /**
     * @param $typeIds
     * @param $provinceCities
     * @return array
     */
    public static function getProvinceCityDistrictName($typeIds, $provinceCities)
    {
        $result = [];
        $data = [];
        if (!$typeIds) {
            return $result;
        }

        foreach ($provinceCities as $provinceCity) {
            $data[$provinceCity['id']] = $provinceCity['name'];
        }

        foreach ($typeIds as $id) {
            $result[] = [
                'id' => (int)$id,
                'name' => $data[$id]
            ];
        }

        return $result;
    }
}
