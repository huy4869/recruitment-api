<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\MJobType;
use App\Models\MProvince;
use App\Models\MProvinceCity;
use App\Models\MProvinceDistrict;
use App\Models\UserLicensesQualification;
use App\Services\Service;
use App\Services\User\Job\JobService;
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
        $applications = Application::query()
            ->with('jobPostingAcceptTrashed', 'jobPostingAcceptTrashed.province', 'jobPostingAcceptTrashed.province.provinceCities')
            ->get();

        $mJobTypes = MJobType::query()->whereIn('id', $jobTypeIds)->get();
        $mProvinces = MProvince::query()->get()->pluck('name', 'id');
        $mProvinceCities = MProvinceCity::query()->get()->pluck('name', 'id');
        $other = $mJobTypes->where('id', MJobType::OTHER)->first();
        $defaultJobTypeIds = $mJobTypes
            ->where('is_default', '=', MJobType::IS_DEFAULT)
            ->where('id', '!=', MJobType::OTHER)
            ->pluck('id', 'id')
            ->toArray();

        $mJobTypes = $mJobTypes
            ->where('is_default', MJobType::IS_DEFAULT)
            ->pluck('name', 'id');

        $tmpJobTypeIds = array_intersect($defaultJobTypeIds, $jobTypeIds);

        if (in_array(MJobType::OTHER, $jobTypeIds)) {
            $tmpJobTypeIds[] = MJobType::OTHER;
        }

        $jobTypeIds = $tmpJobTypeIds;

        foreach ($jobTypeIds as $jobTypeId) {
            $provinceAccordingJobTypes[$jobTypeId] = [];
        }

        foreach ($applications as $application) {
            if ($application->jobPostingAcceptTrashed->province->district_id == MProvinceDistrict::HOKKAIDO) {
                $locationId = 'city_' . $application->jobPostingAcceptTrashed->province_city_id;
            } else {
                $locationId = $application->jobPostingAcceptTrashed->province_id;
            }

            foreach ($application->jobPostingAcceptTrashed->job_type_ids as $jobTypeId) {
                if ($locationId) {
                    if (!isset($defaultJobTypeIds[$jobTypeId])) {
                        if (!isset($provinceAccordingJobTypes[MJobType::OTHER][$locationId])) {
                            $provinceAccordingJobTypes[MJobType::OTHER][$locationId] = 1;
                        } else {
                            $provinceAccordingJobTypes[MJobType::OTHER][$locationId]++;
                        }
                    } else if (!isset($provinceAccordingJobTypes[$jobTypeId][$locationId])) {
                        $provinceAccordingJobTypes[$jobTypeId][$locationId] = 1;
                    } else {
                        $provinceAccordingJobTypes[$jobTypeId][$locationId]++;
                    }
                }
            }
        }

        foreach ($provinceAccordingJobTypes as $jobType => $provinceAccordingJobType) {
            asort($provinceAccordingJobType, SORT_DESC);

            $locations = array_slice($provinceAccordingJobType, 0, is_null($limit) ? self::DEFAULT_LIMIT_LOCATION : $limit, true);
            $locationName = isset($mJobTypes[$jobType]) ? $mJobTypes[$jobType] . trans('job_posting.search_in_popular_area') : $other['name'];
            $list[$locationName] = [];


            foreach ($locations as $id => $count) {
                preg_match("/^city_(\d+)$/", $id, $match);

                $list[$locationName][] = [
                    'id' => count($match) ? (int)$match[1] : $id,
                    'name' => count($match) ? $mProvinceCities[$match[1]] : $mProvinces[$id],
                    'is_city' => !!count($match),
                ];
            }
        }

        return $list;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAmountJobInProvince()
    {
        $user = $this->user;
        $jobApplicationIds = $user ? JobService::getIdJobApplicationCancelOrReject($this->user) : [];

        return DB::table('m_provinces_cities')
            ->select('m_provinces.id as province_id', DB::raw('count(distinct job_postings.id) as amount_job'))
            ->join('m_provinces', 'm_provinces_cities.province_id', '=', 'm_provinces.id')
            ->leftJoin('job_postings', function ($join) use ($jobApplicationIds) {
                $join->on('job_postings.province_id', '=', 'm_provinces.id')
                    ->whereNot('job_postings.id', $jobApplicationIds)
                    ->where('job_postings.job_status_id', '=', JobPosting::STATUS_RELEASE)
                    ->whereNull('deleted_at');
            })
            ->groupBy('m_provinces.id')
            ->get();
    }
}
