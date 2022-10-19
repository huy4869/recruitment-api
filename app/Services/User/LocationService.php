<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\MJobType;
use App\Models\MProvince;
use App\Models\UserLicensesQualification;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LocationService extends Service
{
    CONST DEFAULT_LIMIT_LOCATION = 10;

    public function getAccordingToMostApply($jobTypeIds = [], $limit = null)
    {
        if (!count($jobTypeIds)) {
            return [];
        }

        $list = [];
        $provinceAccordingJobTypes = [];
        $applications = Application::query()->with('jobPosting', 'jobPosting.province')->get();
        $mJobTypes = MJobType::query()->get();
        $mProvinces = MProvince::query()->get()->pluck('name', 'id');
        $defaultJobTypeIds = $mJobTypes->where('is_default', MJobType::IS_DEFAULT)->pluck('id', 'id')->toArray();
        $mJobTypes = $mJobTypes->where('is_default', MJobType::IS_DEFAULT)->pluck('name', 'id');

        $tmpJobTypeIds = array_intersect($defaultJobTypeIds, $jobTypeIds);

        if (in_array(MJobType::OTHER, $jobTypeIds)) {
            $tmpJobTypeIds[] = MJobType::OTHER;
        }

        $jobTypeIds = $tmpJobTypeIds;

        foreach ($jobTypeIds as $jobTypeId) {
            $provinceAccordingJobTypes[$jobTypeId] = [];
        }

        foreach ($applications as $application) {
            foreach ($application->jobPosting->job_type_ids as $jobTypeId) {
                if ($application->jobPosting->province_id) {
                    if (!isset($defaultJobTypeIds[$jobTypeId])) {
                        if (!isset($provinceAccordingJobTypes[MJobType::OTHER][$application->jobPosting->province_id])) {
                            $provinceAccordingJobTypes[MJobType::OTHER][$application->jobPosting->province_id] = 1;
                        } else {
                            $provinceAccordingJobTypes[MJobType::OTHER][$application->jobPosting->province_id]++;
                        }
                    } else if (!isset($provinceAccordingJobTypes[$jobTypeId][$application->jobPosting->province_id])) {
                        $provinceAccordingJobTypes[$jobTypeId][$application->jobPosting->province_id] = 1;
                    } else {
                        $provinceAccordingJobTypes[$jobTypeId][$application->jobPosting->province_id]++;
                    }
                }
            }
        }

        foreach ($provinceAccordingJobTypes as $jobType => $provinceAccordingJobType) {
            asort($provinceAccordingJobType, SORT_DESC);
            $provinces = array_slice($provinceAccordingJobType, 0, is_null($limit) ? self::DEFAULT_LIMIT_LOCATION : $limit, true);
            $list[$jobType == MJobType::OTHER ? 'その他' : $mJobTypes[$jobType]] = [];

            foreach ($provinces as $id => $count) {
                $list[$jobType == MJobType::OTHER ? 'その他' : $mJobTypes[$jobType]][] = [
                    'id' => $id,
                    'name' => $mProvinces[$id],
                ];
            }
        }

        return $list;
    }
}
