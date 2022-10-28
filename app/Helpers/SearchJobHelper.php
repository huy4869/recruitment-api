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
            $content['feature_ids'] = JobHelper::getFeatureCategoryName(
                $content['feature_ids'],
                $masterData['masterJobFeatures'],
            );
        }

        if (isset($content['province_id'])) {
            $content['province_id'] = JobHelper::getProvinceDistrictName(
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

        if (isset($content['order_by_ids'])) {
            $content['order_by_ids'] = JobHelper::getTypeName(
                $content['order_by_ids'],
                config('order_by.job_posting')
            );
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

        if (!$typeIds) {
            return $result;
        }

        foreach ($typeIds as $id) {
            $provinceCity = $provinceCities[$id - 1];

            $result[] = [
                'district' => [
                    'id' => $provinceCity['province']['province_district']['id'],
                    'name' => $provinceCity['province']['province_district']['name'],
                    'province' => [
                        'id' => $provinceCity['province']['id'],
                        'name' => $provinceCity['province']['name'],
                        'province_city' => [
                            'id' => $provinceCity['id'],
                            'name' => $provinceCity['name']
                        ]
                    ]
                ],
            ];
        }

        return $result;
    }
}
