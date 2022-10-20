<?php

namespace App\Services\Recruiter;

use App\Models\JobPosting;
use App\Services\MasterDataService as BaseMasterDataService;

class MasterDataService extends BaseMasterDataService
{
    /**
     * @var array
     */
    protected $availableResources = [
        'jobs' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getAllJobName',
        ],
    ];

    /**
     * @return array
     */
    protected function getAllJobName()
    {
        $recruiter = auth()->user();

        if (!$recruiter) {
            return [];
        }

        $recruiterStoreIds = $recruiter->stores->pluck('id')->toArray();
        $jobs = JobPosting::query()->whereIn('store_id', $recruiterStoreIds)->get();
        $result = [];

        foreach ($jobs as $job) {
            $result[] = [
                'id' => $job->id,
                'name' => $job->name,
            ];
        }

        return $result;
    }
}
