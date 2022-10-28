<?php

namespace App\Services\User;

use App\Models\JobPosting;
use App\Models\MJobType;
use App\Services\Service;

class JobTypeService extends Service
{
    /**
     * amount job in work type
     *
     * @return array
     */
    public function amountJobInJobTypes()
    {
        $jobTypes =  MJobType::query()->where('is_default', '=', MJobType::IS_DEFAULT)->get()->pluck('id')->toArray();
        $jobPostings = JobPosting::query()->released()->get()->pluck('job_type_ids')->toArray();
        $data = [];

        $jobPostings = array_count_values(array_merge(...$jobPostings));
        foreach ($jobTypes as $jobType) {
            $data[] = [
                'id' => $jobType,
                'amount' => $jobPostings[$jobType] ?? 0,
            ];
            unset($jobPostings[$jobType]);
        }

        return array_merge($data, [[
           'id' => 'other',
           'amount' => array_sum($jobPostings),
        ]]);
    }
}
