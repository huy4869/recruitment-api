<?php

namespace App\Services\User\Job;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\JobHelper;
use App\Http\Resources\User\Job\JobFavoriteResource;
use App\Models\FavoriteJob;
use App\Models\MJobType;
use App\Models\MWorkType;
use App\Models\JobPosting;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class JobService extends Service
{
    /**
     * get Favorite Jobs
     *
     * @return array
     */
    public function getFavoriteJobs()
    {
        $data = DB::table('favorite_jobs')
            ->join('job_postings', 'job_postings.id', '=', 'favorite_jobs.job_posting_id')
            ->leftJoin('applications', 'applications.job_posting_id', '=', 'job_postings.id')
            ->leftJoin('m_interviews_status', 'm_interviews_status.id', '=', 'applications.interview_status_id')
            ->join('stores', 'stores.id', '=', 'job_postings.store_id')
            ->join('m_provinces', 'm_provinces.id', '=', 'job_postings.province_id')
            ->join('m_salary_types', 'm_salary_types.id', '=', 'job_postings.salary_type_id')
            ->where('favorite_jobs.user_id', $this->user->id)
            ->where(function ($query) {
                $query->where('applications.user_id', $this->user->id)
                    ->orWhere('applications.user_id', '=', null);
            })
            ->select(
                'favorite_jobs.id',
                'favorite_jobs.job_posting_id',
                'job_postings.name as job_name',
                'stores.name as store_name',
                'm_interviews_status.name as interview_name',
                'job_postings.postal_code',
                'm_provinces.name as province_name',
                'job_postings.city',
                'job_postings.address',
                'job_postings.job_type_ids',
                'job_postings.salary_min',
                'job_postings.salary_max',
                'm_salary_types.name as salary_name',
                'job_postings.start_work_time',
                'job_postings.end_work_time',
                'job_postings.holiday_description',
                'job_postings.description',
                'job_postings.work_type_ids'
            )
            ->orderByDesc('id')
            ->paginate(config('paginate.USER_015.favorite_job.limit_per_page'));

        return [
            'total' => $data->total(),
            'favorite' => JobFavoriteResource::collection($data),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'path' => $data->path,
                'total_page' => $data->lastPage(),
            ],
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
        $jobCount = $jobs->count();
        $jobList = $jobs->with([
                'store',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->take(config('common.job_posting_newest_amount'))
            ->get();

        $masterData = JobHelper::getJobMasterData($this->user);
        $result = [];

        foreach ($jobList as $job) {
            $result[] = JobHelper::addFormatJobJsonData($job, $masterData);
        }

        return [
            'total_jobs' => $jobCount,
            'list_jobs' => $result,
        ];
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
            ->take(config('common.job_posting_most_view_amount'))
            ->get();

        $masterData = JobHelper::getJobMasterData($this->user);
        $result = [];

        foreach ($jobList as $job) {
            $result[] = JobHelper::addFormatJobJsonData($job, $masterData);
        }

        return $result;
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
            ->take(config('common.job_posting_most_applies'))
            ->get();

        $masterData = JobHelper::getJobMasterData($this->user);
        $result = [];

        foreach ($jobList as $job) {
            $result[] = JobHelper::addFormatJobJsonData($job, $masterData);
        }

        return $result;
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
}
