<?php

namespace App\Services\User;

use App\Helpers\JobHelper;
use App\Models\DesiredConditionUser;
use App\Models\UserJobDesiredMatch;
use App\Services\Service;

class UserJobDesiredMatchService extends Service
{
    /**
     *
     * @return array
     */
    public function getListMatch()
    {
        $res =  UserJobDesiredMatch::query()
            ->with('job', function ($q) {
                return $q->orderBy('released_at', 'DESC');
            })
            ->where('user_id', $this->user->id)
            ->orderBy('suitability_point', 'DESC')
            ->take(config('common.job_posting.recommend'))
            ->get();

        $jobPostings = $res->map(function ($item) {
            return $item->job;
        });

        $masterData = JobHelper::getJobMasterData($this->user);
        $result = [];

        foreach ($jobPostings as $job) {
            $result[] = JobHelper::addFormatJobJsonData($job, $masterData);
        }

        return $result;
    }
}
