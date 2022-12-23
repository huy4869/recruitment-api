<?php

namespace App\Services\User;

use App\Helpers\JobHelper;
use App\Models\DesiredConditionUser;
use App\Models\JobPosting;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\UserJobDesiredMatch;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class UserJobDesiredMatchService extends Service
{
    public static function getIdJobApplication($user)
    {
        return $user->applications()->pluck('job_posting_id')->toArray();
    }

    /**
     *
     * @return array
     */
    public function getListMatch()
    {
        $jobPostingIds = [];

        if ($this->user) {
            $jobPostingIds = self::getIdJobApplication($this->user);
        }

        $res = UserJobDesiredMatch::query()
            ->join('job_postings', 'user_job_desired_matches.job_id', '=', 'job_postings.id')
            ->where('job_status_id', JobPosting::STATUS_RELEASE)
            ->where('user_job_desired_matches.user_id', $this->user->id)
            ->with([
                'job' => function ($query) use ($jobPostingIds) {
                    $query->whereNotIn('id', $jobPostingIds);
                },
                'job.store',
                'job.store.owner',
            ])
            ->orderBy('suitability_point', 'DESC')
            ->orderBy('released_at', 'DESC')
            ->take(config('common.job_posting.recommend'))
            ->get();

        $jobPostings = $res->map(function ($item) {
            return $item->job;
        });

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];
        $masterData = JobHelper::getJobMasterData($needMasterData);
        $userAction = JobHelper::getUserActionJob($this->user);
        $result = [];

        foreach ($jobPostings as $job) {
            if (!is_null($job)) {
                $result[] = JobHelper::addFormatJobJsonData($job, $masterData, $userAction);
            }
        }

        return $result;
    }
}
