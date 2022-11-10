<?php

namespace App\Services\User;

use App\Helpers\JobHelper;
use App\Models\DesiredConditionUser;
use App\Models\JobPosting;
use App\Models\UserJobDesiredMatch;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class UserJobDesiredMatchService extends Service
{
    /**
     *
     * @return array
     */
    public function getListMatch()
    {
        $res = UserJobDesiredMatch::query()
            ->join('job_postings', 'user_job_desired_matches.job_id', '=', 'job_postings.id')
            ->where('job_status_id', JobPosting::STATUS_RELEASE)
            ->where('user_job_desired_matches.user_id', $this->user->id)
            ->orderBy('suitability_point', 'DESC')
            ->orderBy('released_at', 'DESC')
            ->take(config('common.job_posting.recommend'))
            ->get();

        $jobPostings = $res->map(function ($item) {
            return $item->job;
        });

        $masterData = JobHelper::getJobMasterData();
        $userAction = JobHelper::getUserActionJob($this->user);
        $result = [];

        foreach ($jobPostings as $job) {
            $result[] = JobHelper::addFormatJobJsonData($job, $masterData, $userAction);
        }

        return $result;
    }
}
