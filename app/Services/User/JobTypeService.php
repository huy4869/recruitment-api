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
        $jobTypes =  MJobType::query()->where('is_default', '=', MJobType::IS_DEFAULT)->get()->pluck('name', 'id')->toArray();
        unset($jobTypes[MJobType::OTHER]);
        $jobPostings = JobPosting::query()->released()->get()->pluck('job_type_ids')->toArray();
        $data = [];

        $jobPostings = array_count_values(array_merge(...$jobPostings));
        foreach ($jobTypes as $key => $jobType) {
            $data[] = [
                'id' => $key,
                'name' => $jobType,
                'amount' => $jobPostings[$key] ?? 0,
            ];
            unset($jobPostings[$key]);
        }

        return array_merge($data, [[
           'id' => MJobType::OTHER,
           'name' => 'その他',
           'amount' => array_sum($jobPostings),
        ]]);
    }
}
