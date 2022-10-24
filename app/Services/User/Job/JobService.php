<?php

namespace App\Services\User\Job;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\JobHelper;
use App\Models\Application;
use App\Models\FavoriteJob;
use App\Models\Gender;
use App\Models\JobPosting;
use App\Models\MJobExperience;
use App\Models\MJobFeature;
use App\Models\MJobType;
use App\Models\MProvince;
use App\Models\MWorkType;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class JobService extends Service
{
    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public function detail($id)
    {
        $user = $this->user;
        $job = JobPosting::query()->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->released()
                    ->orWhere(function ($query) use ($user) {
                        if ($user) {
                            $query->where('job_status_id', JobPosting::STATUS_END)
                                ->whereHas('applications', function ($query) use ($user) {
                                    $query->whereNotIn('interview_status_id', [Application::STATUS_APPLYING, Application::STATUS_CANCELED])
                                        ->where('user_id', $user->id);
                                });
                        }
                    });
            })
            ->first();

        if (!$job) {
            throw new InputException(trans('response.not_found'));
        }

        $job->with([
            'store',
            'bannerImage',
            'detailImages',
            'province',
            'province.provinceDistrict',
            'salaryType',
        ])
        ->get();

        $masterData = JobHelper::getJobMasterData();
        $userAction = JobHelper::getUserActionJob($user);
        $jobData = JobHelper::addFormatJobJsonData($job, $masterData, $userAction);

        if (!$user) {
            $job->update(['views' => DB::raw('`views` + 1')]);

            return $jobData;
        }

        try {
            DB::beginTransaction();

            $job->update(['views' => DB::raw('`views` + 1')]);

            $userRecentJobs = self::userRecentJobsUpdate($job->id, $user->recent_jobs);
            $user->update(['recent_jobs' => $userRecentJobs]);

            DB::commit();
            return $jobData;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param $ids
     * @return array
     * @throws InputException
     */
    public function getRecentJobs($ids)
    {
        if ($this->user) {
            $jobIds = $this->user->recent_jobs ?? [];
        } else {
            $jobIds = array_map('intval', explode(',', $ids));
        }

        if (!is_array($jobIds)) {
            throw new InputException(trans('response.invalid'));
        }

        $jobList = JobPosting::query()->released()
            ->whereIn('id', $jobIds)
            ->with([
                'store',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->take(config('common.job_posting.recent_amount'))
            ->get();

        $jobIdsAvailable = $jobList->pluck('id')->toArray();
        $jobIds = array_diff($jobIds, array_diff($jobIds, $jobIdsAvailable));
        $this->user->update(['recent_jobs' => array_values($jobIds)]);

        $masterData = JobHelper::getJobMasterData();
        $userAction = JobHelper::getUserActionJob($this->user);
        $jobArr = [];

        foreach ($jobList as $job) {
            $jobArr[$job->id] = $job;
        }

        $jobIds = collect($jobIds);
        $jobIds->shift();

        return $jobIds->map(function ($id) use ($jobArr, $masterData, $userAction) {
            return JobHelper::addFormatJobJsonData($jobArr[$id], $masterData, $userAction);
        })->toArray();
    }

    /**
     * @return array
     * @throws InputException
     */
    public function getSuggestJobs($id)
    {
        $job = JobPosting::query()->where('id', $id)
            ->released()
            ->first();

        if (!$job) {
            throw new InputException(trans('response.not_found'));
        }

        $queryType = '';
        $jobAlias = '';

        foreach ($job->job_type_ids as $key => $type) {
            $queryType = $queryType . sprintf('json_contains(job_type_ids, \'"%u"\') as job%u, ', $type, $type);
            $jobAlias = $jobAlias . sprintf('job%u + ', $type);
        }

        $querySuggestJobs = sprintf(
            '(SELECT id, released_at, province_id, %sIF(province_id = %u, %u, 0) as provinceRatio
            FROM job_postings
            WHERE id != %u
            ) as tmp',
            $queryType,
            $job->province_id,
            config('common.job_posting.province_ratio'),
            $job->id,
        );

        $jobIds = DB::table(DB::raw($querySuggestJobs))
        ->select('id', 'released_at', DB::raw($jobAlias . 'provinceRatio as total'))
        ->orderByRaw('total DESC')
        ->orderByRaw('released_at DESC')
        ->limit(config('common.job_posting.suggest_amount'))
        ->get()
        ->pluck('id')
        ->toArray();

        $jobList = JobPosting::query()->released()
            ->whereIn('id', $jobIds)
            ->with([
                'store',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->get();

        $masterData = JobHelper::getJobMasterData();
        $userAction = JobHelper::getUserActionJob($this->user);
        $jobArr = [];

        foreach ($jobList as $job) {
            $jobArr[$job->id] = $job;
        }

        return collect($jobIds)->map(function ($id) use ($jobArr, $masterData, $userAction) {
            return JobHelper::addFormatJobJsonData($jobArr[$id], $masterData, $userAction);
        })->toArray();
    }

    /**
     * @return array
     */
    public function getList($relations = [])
    {
        $query = JobPosting::query()->released();

        if (count($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }
    /**
     * get Favorite Jobs
     *
     * @return array
     */
    public function getFavoriteJobs()
    {
        $userId = $this->user->id;
        $favoriteJob = FavoriteJob::where('favorite_jobs.user_id', $this->user->id)
            ->with([
                'jobPosting',
                'jobPosting.applications' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhere('user_id', '=', null);
                },
                'jobPosting.applications.interviews',
                'jobPosting.store',
                'jobPosting.province',
                'jobPosting.salaryType',
                'jobPosting.bannerImage'
            ])
            ->orderByDesc('id')
            ->paginate(config('paginate.USER_015.favorite_job.limit_per_page'));

        $masterData = JobHelper::getJobMasterData($this->user);
        $result = [];

        foreach ($favoriteJob as $job) {
            $result[] = JobHelper::addFormatFavoriteJsonData($job, $masterData);
        }

        return [
            'paginate' => [
                'currentPage' => $favoriteJob->currentPage(),
                'path' => $favoriteJob->path(),
                'totalPage' => $favoriteJob->lastPage(),
                'total' => $favoriteJob->total(),
            ],
            'favoriteJob' => $result,
        ];
    }

    /**
     * Get total new jobs
     *
     * @return array
     */
    public function getListNewJobPostings()
    {
        $jobs = JobPosting::query()->released()->new();
        $jobList = $jobs->with([
                'store',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->take(config('common.job_posting.newest_amount'))
            ->get();

        $result = $this->appendMaster($this->user, $jobList);

        return [
            'total_jobs' => $jobList->count(),
            'list_jobs' => $result,
        ];
    }

    public static function appendMaster($user, $jobs)
    {
        $masterData = JobHelper::getJobMasterData();
        $userAction = JobHelper::getUserActionJob($user);
        $result = [];

        foreach ($jobs as $job) {
            $result[] = JobHelper::addFormatJobJsonData($job, $masterData, $userAction);
        }

        return $result;
    }

    /**
     * Get most view jobs
     *
     * @return array
     */
    public function getListMostViewJobPostings()
    {
        $jobList = JobPosting::query()->released()
            ->with([
                'store',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->orderby('views', 'desc')
            ->orderBy('released_at', 'desc')
            ->take(config('common.job_posting.most_view_amount'))
            ->get();

        return $this->appendMaster($this->user, $jobList);
    }

    /**
     * Get most apply jobs
     *
     * @return array
     */
    public function getListMostApplyJobPostings()
    {
        $jobList = JobPosting::query()->released()
            ->with([
                'store',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->orderby('applies', 'desc')
            ->orderBy('released_at', 'desc')
            ->take(config('common.job_posting.most_applies'))
            ->get();

        return $this->appendMaster($this->user, $jobList);
    }

    /**
     * delete favorite job
     *
     * @return bool|null
     * @throws InputException
     */
    public function deleteFavorite($id)
    {
        $data = FavoriteJob::where('user_id', $this->user->id)->find($id);

        if ($data) {
            return $data->delete();
        }

        throw new InputException(trans('validation.ERR.exist.favorite_job'));
    }

    /**
     * Get job posting type
     *
     * @return array
     */
    public static function getMasterDataJobPostingWorkTypes()
    {
        $workTypes = MWorkType::all();

        return CommonHelper::getMasterDataIdName($workTypes);
    }

    /**
     * Get job posting name
     *
     * @return array
     */
    public static function getMasterDataJobPostingTypes()
    {
        $jobTypes = MJobType::all();

        return CommonHelper::getMasterDataIdName($jobTypes);
    }

    /**
     * @return array
     */
    public static function getMasterDataJobGenders()
    {
        $gender = Gender::all();

        return CommonHelper::getMasterDataIdName($gender);
    }

    /**
     * @return array
     */
    public static function getMasterDataJobExperiences()
    {
        $experiences = MJobExperience::all();

        return CommonHelper::getMasterDataIdName($experiences);
    }

    /**
     * @return array
     */
    public static function getMasterDataJobFeatures()
    {
        return MJobFeature::query()->with(['category'])->get()->toArray();
    }

    /**
     * @return array
     */
    public static function getMasterDataProvinces()
    {
        return MProvince::query()->with(['provinceDistrict'])->get()->toArray();
    }

    /**
     * Check job posting is favorite job
     *
     * @param $user
     * @return Builder[]|false
     */
    public static function getUserFavoriteJobIds($user)
    {
        if (!$user) {
            return false;
        }

        return FavoriteJob::query()->where('user_id', $user->id)
            ->pluck('job_posting_id')
            ->toArray();
    }

    /**
     * Get user apply job ids
     *
     * @param $user
     * @return Builder[]|false
     */
    public static function getUserApplyJobIds($user)
    {
        if (!$user) {
            return false;
        }

        return Application::query()->where('user_id', $user->id)
            ->pluck('job_posting_id')
            ->toArray();
    }

    /**
     * @param $jobId
     * @param $userRecentJobs
     * @return array|mixed
     */
    public static function userRecentJobsUpdate($jobId, $userRecentJobs)
    {
        if (!$userRecentJobs) {
            $userRecentJobs = [];
        }

        if (empty($userRecentJobs)) {
            return array(sprintf('%u', $jobId));
        }

        if ($jobId == $userRecentJobs[0]) {
            return $userRecentJobs;
        }

        if (($key = array_search($jobId, $userRecentJobs)) !== false) {
            unset($userRecentJobs[$key]);
        }

        if (count($userRecentJobs) >= config('common.job_posting.recent_jobs_limit')) {
            array_pop($userRecentJobs);
        }

        return array_merge([
            sprintf('%u', $jobId)
        ], $userRecentJobs);
    }
}
