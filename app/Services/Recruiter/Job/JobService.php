<?php

namespace App\Services\Recruiter\Job;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\JobHelper;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobService extends Service
{
    /**
     * @param $id
     * @return bool
     * @throws InputException
     * @throws Exception
     */
    public function destroy($id)
    {
        $job = JobPosting::query()->where('id', $id)->first();

        if (!$job) {
            throw new InputException(trans('response.invalid'));
        }

        try {
            DB::beginTransaction();

            $job->applications()->delete();
            $job->applicationUserWorkHistory()->delete();
            $job->applicationUserLearningHistory()->delete();
            $job->favoriteJobs()->delete();
            $job->feedbacks()->delete();
            $job->userJobDesiredMatch()->delete();
            $job->images()->delete();
            $job->delete();

            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @param $jobList
     * @return array
     */
    public static function getJobInfoForListJob($jobList)
    {
        $jobMasterData = JobHelper::getJobMasterData();
        $jobArr = [];

        foreach ($jobList as $job) {
            $job->job_types = JobHelper::getTypeName(
                $job->job_type_ids,
                $jobMasterData['masterJobTypes']
            );
            $job->work_types = JobHelper::getTypeName(
                $job->work_type_ids,
                $jobMasterData['masterWorkTypes']
            );

            $jobArr[$job->id] = $job;
        }//end foreach

        return $jobArr;
    }
}
