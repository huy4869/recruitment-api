<?php

namespace App\Services\User\Job;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\JobHelper;
use App\Helpers\ResponseHelper;
use App\Models\Application;
use App\Models\FavoriteJob;
use App\Models\Gender;
use App\Models\JobPosting;
use App\Models\MInterviewApproach;
use App\Models\MInterviewStatus;
use App\Models\MJobExperience;
use App\Models\MJobFeature;
use App\Models\MJobType;
use App\Models\MProvince;
use App\Models\MStation;
use App\Models\MWorkType;
use App\Models\Notification;
use App\Models\Store;
use App\Models\StoreOffTime;
use App\Services\Service;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class JobService extends Service
{
    /**
     * @param $id
     * @return array | int
     * @throws Exception
     */
    public function detail($id)
    {
        $user = $this->user;
        $job = JobPosting::query()->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->whereIn('job_status_id', [JobPosting::STATUS_RELEASE, JobPosting::STATUS_DRAFT, JobPosting::STATUS_HIDE, JobPosting::STATUS_END])
                    ->orWhere(function ($query) use ($user) {
                        if ($user) {
                            $query->where('job_status_id', JobPosting::STATUS_END)
                                ->whereHas('applications', function ($query) use ($user) {
                                    $query->whereNotIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_CANCELED])
                                        ->where('user_id', $user->id);
                                });
                        }
                    });
            })
            ->with([
                'storeTrashed',
                'storeTrashed.owner' => function ($q) {
                    $q->withTrashed();
                },
                'storeTrashed.province',
                'storeTrashed.provinceCity',
                'bannerImage',
                'detailImages',
                'province',
                'province.provinceDistrict',
                'provinceCity',
                'salaryType',
            ]);

        if ($user) {
            $job = $job->with([
                'applications' => function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                },
                'applications.interviews',
                'applications.interviewApproach',
            ]);
        }

        $job = $job->withTrashed()->first();

        if (!$job) {
            return null;
        }

        $masterData = JobHelper::getJobMasterData();
        $userAction = JobHelper::getUserActionJob($user);
        $jobData = JobHelper::addFormatJobJsonData($job, $masterData, $userAction);

        if (!$user) {
            $job->withoutTimestamps()->update(['views' => DB::raw('`views` + 1')]);

            return $jobData;
        }

        try {
            DB::beginTransaction();

            $job->withoutTimestamps()->update(['views' => DB::raw('`views` + 1')]);

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
        $applicationIds = [];

        if ($this->user) {
            $jobIds = $this->user->recent_jobs ?? [];
            $applicationIds = self::getIdJobApplication($this->user);
        } else {
            $jobIds = array_map('intval', explode(',', $ids));
        }

        if (!is_array($jobIds)) {
            throw new InputException(trans('response.invalid'));
        }

        $jobList = JobPosting::query()->released()
            ->whereIn('id', $jobIds)
            ->whereNotIn('id', $applicationIds)
            ->with([
                'store',
                'store.owner',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->take(config('common.job_posting.recent_amount'))
            ->get();

        $jobIdsAvailable = $jobList->pluck('id')->toArray();
        $jobIds = array_diff($jobIds, array_diff($jobIds, $jobIdsAvailable));
        $this->user?->update(['recent_jobs' => array_values($jobIds)]);

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];
        $masterData = JobHelper::getJobMasterData($needMasterData);
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
            '(SELECT id, job_status_id, released_at, deleted_at, province_id, %sIF(province_id = %u, %u, 0) as provinceRatio
            FROM job_postings
            WHERE id != %u
            ) as tmp',
            $queryType,
            $job->province_id,
            config('common.job_posting.province_ratio'),
            $job->id,
        );

        $applicationIds = [];

        if ($this->user) {
            $applicationIds = self::getIdJobApplication($this->user);
        }

        $jobIds = DB::table(DB::raw($querySuggestJobs))
            ->select('id', 'released_at', DB::raw($jobAlias . 'provinceRatio as total'))
            ->where('job_status_id', JobPosting::STATUS_RELEASE)
            ->where('deleted_at', null)
            ->whereNotIn('id', $applicationIds)
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
                'store.owner',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->get();

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];
        $masterData = JobHelper::getJobMasterData($needMasterData);
        $userAction = JobHelper::getUserActionJob($this->user);
        $jobArr = [];

        foreach ($jobList as $job) {
            $jobArr[$job->id] = $job;
        }

        return collect($jobIds)->map(function ($id) use ($jobArr, $masterData, $userAction) {
            return JobHelper::addFormatJobJsonData($jobArr[$id], $masterData, $userAction);
        })->toArray();
    }

    public static function getIdJobApplication($user)
    {
        return $user->applications()->pluck('job_posting_id')->toArray();
    }

    public static function getIdJobApplicationCancelOrReject($user)
    {
        return $user->applications()->whereIn('interview_status_id', [MInterviewStatus::STATUS_CANCELED, MInterviewStatus::STATUS_REJECTED])->pluck('job_posting_id')->toArray();
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
    public function getFavoriteJobs($perPage)
    {
        $perPage = $perPage ?: config('paginate.USER_015.favorite_job.limit_per_page');
        $userId = $this->user->id;
        $favoriteJob = FavoriteJob::where('favorite_jobs.user_id', $this->user->id)
            ->with([
                'jobPostingTrashed',
                'jobPostingTrashed.applications' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhere('user_id', '=', null);
                },
                'jobPostingTrashed.applications.interviews',
                'jobPostingTrashed.storeTrashed',
                'jobPostingTrashed.storeTrashed.owner',
                'jobPostingTrashed.provinceCity',
                'jobPostingTrashed.province',
                'jobPostingTrashed.province.provinceDistrict',
                'jobPostingTrashed.salaryType',
                'jobPostingTrashed.bannerImage'
            ])
            ->orderByDesc('id')
            ->paginate($perPage);

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];
        $masterData = JobHelper::getJobMasterData($needMasterData);
        $result = [];

        foreach ($favoriteJob as $job) {
            $result[] = self::addFormatFavoriteJsonData($job, $masterData);
        }

        return [
            'per_page' => $perPage,
            'current_page' => $favoriteJob->currentPage(),
            'total_page' => $favoriteJob->lastPage(),
            'total' => $favoriteJob->total(),
            'favoriteJob' => $result,
        ];
    }


    /**
     * @param $favoriteJob
     * @param $masterData
     * @return array
     */
    public static function addFormatFavoriteJsonData($favoriteJob, $masterData)
    {
        $jobPosting = $favoriteJob->jobPostingTrashed;
        $workTypes = JobHelper::getTypeName($jobPosting->work_type_ids, $masterData['masterWorkTypes']);
        $jobTypes = JobHelper::getTypeName($jobPosting->job_type_ids, $masterData['masterJobTypes']);

        return array_merge($favoriteJob->toArray(), [
            'work_types' => $workTypes,
            'job_types' => $jobTypes,
        ]);
    }

    /**
     * Get total new jobs
     *
     * @return array
     */
    public function getListNewJobPostings()
    {
        $applicationRejectOrCancelIds = [];
        $applicationIds = [];

        if ($this->user) {
            $applicationIds = self::getIdJobApplication($this->user);
        }

        $jobList = JobPosting::query()->new()
            ->released()
            ->whereNotIn('id', $applicationIds);

        $jobCount = $jobList->count();

        $jobList = $jobList
            ->with([
                'store',
                'store.owner',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])->orderBy('released_at', 'desc')
            ->take(config('common.job_posting.newest_amount'))
            ->get();

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];

        $result = $this->appendMaster($this->user, $jobList, $needMasterData);

        return [
            'total_jobs' => JobHelper::thousandNumberFormat($jobCount),
            'list_jobs' => $result,
        ];
    }

    public static function appendMaster($user, $jobs, $needMasterData = [])
    {
        $masterData = JobHelper::getJobMasterData($needMasterData);
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
        $applicationIds = [];

        if ($this->user) {
            $applicationIds = self::getIdJobApplication($this->user);
        }

        $jobList = JobPosting::query()->released()
            ->with([
                'store',
                'store.owner',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->whereNotIn('id', $applicationIds)
            ->orderby('views', 'desc')
            ->orderBy('released_at', 'desc')
            ->take(config('common.job_posting.most_view_amount'))
            ->get();

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];

        return $this->appendMaster($this->user, $jobList, $needMasterData);
    }

    /**
     * Get most favorite jobs
     *
     * @return array
     */
    public function getListMostFavoriteJobPostings()
    {
        $jobList = JobPosting::query()
            ->select('job_postings.*', DB::raw('COUNT(favorite_jobs.id) as total_favorites'))
            ->join('favorite_jobs', 'job_postings.id', '=', 'favorite_jobs.job_posting_id')
            ->with([
                'store',
                'store.owner',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->groupBy('job_postings.id')
            ->orderBy('total_favorites', 'desc')
            ->take(config('common.job_posting.most_applies'))
            ->get();

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];

        return $this->appendMaster($this->user, $jobList, $needMasterData);
    }

    /**
     * delete favorite job
     *
     * @return bool|null
     * @throws InputException
     */
    public function deleteFavorite($id)
    {
        $favorite = $this->user->favoriteJobs()->withTrashed()->where('job_posting_id', $id)->delete();

        if ($favorite) {
            return true;
        }

        throw new InputException(trans('response.not_found'));
    }

    public function getListMostFavoriteJob() {
        $jobList = JobPosting::query()
            ->select('job_postings.*', DB::raw('COUNT(favorite_jobs.id) as total_favorites'))
            ->join('favorite_jobs', 'job_postings.id', '=', 'favorite_jobs.job_posting_id')
            ->with([
                'store',
                'store.owner',
                'province',
                'province.provinceDistrict',
                'salaryType',
            ])
            ->whereNull('favorite_jobs.deleted_at')
            ->released()
            ->groupBy('job_postings.id')
            ->orderBy('total_favorites', 'desc')
            ->orderBy('job_postings.released_at', 'desc')
            ->take(config('common.job_posting.most_applies'))
            ->get();

        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];

        return $this->appendMaster($this->user, $jobList, $needMasterData);
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
     * @return array
     */
    public static function getMasterDataStations()
    {
        return MStation::query()->get()->toArray();
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

    /**
     * create store
     *
     * @param $jobPostingId
     * @return mixed
     * @throws InputException
     */
    public function storeFavorite($jobPostingId)
    {
        $userFavoriteByRec = self::getUserFavoriteByRec($jobPostingId);
        $user = $this->user;
        $jobPosting = JobPosting::with('store', 'store.owner')->where('id', $jobPostingId)->first();
        $storeIds = Store::where('user_id', $jobPosting->store->owner->id)->pluck('id')->toArray();
        $jobPostingIds = JobPosting::whereIn('store_id', $storeIds)->pluck('id')->toArray();
        $isFavoriteJob = FavoriteJob::where('user_id', $user->id)->whereIn('job_posting_id', $jobPostingIds)->get();

        try {
            DB::beginTransaction();

            $favoriteJob = FavoriteJob::updateOrCreate([
                'user_id' => $user->id,
                'job_posting_id' => $jobPostingId
            ]);

            if (in_array($user->id, $userFavoriteByRec->pluck('favorite_user_id')->toArray()) && !count($isFavoriteJob)) {
                $data = [
                    [
                        'user_id' => $userFavoriteByRec->first()->user_id,
                        'notice_type_id' => Notification::TYPE_MATCHING_FAVORITE,
                        'noti_object_ids' => json_encode([
                            'store_id' => $jobPosting->store->id,
                            'application_id' => null,
                            'user_id' => $user->id,
                            'job_posting_id' => $jobPostingId
                        ]),
                        'title' => trans('notification.N009.title', [
                            'user_name' => sprintf('%s %s', $user->first_name, $user->last_name),
                        ]),
                        'content' => trans('notification.N009.content', [
                            'user_name' => sprintf('%s %s', $user->first_name, $user->last_name),
                        ]),
                        'created_at' => now(),
                    ],
                    [
                        'user_id' => $user->id,
                        'notice_type_id' => Notification::TYPE_MATCHING_FAVORITE,
                        'noti_object_ids' => json_encode([
                            'store_id' => $jobPosting->store->id,
                            'application_id' => null,
                            'user_id' => $userFavoriteByRec->first()->user_id,
                            'job_id' => $jobPostingId
                        ]),
                        'title' => trans('notification.N010.title', [
                            'store_name' => $jobPosting->store->name,
                        ]),
                        'content' => trans('notification.N010.content', [
                            'store_name' => $jobPosting->store->name,
                        ]),
                        'created_at' => now(),
                    ]
                ];

                Notification::insert($data);
            }//end if

            DB::commit();

            return $favoriteJob;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);

            throw new InputException($e);
        }//end try
    }

    /**
     * @param $jobPostingId
     * @return HigherOrderBuilderProxy|mixed
     */
    public static function getUserFavoriteByRec($jobPostingId)
    {
        $jobPosting = JobPosting::query()
            ->where('id', $jobPostingId)
            ->released()
            ->with([
                'store',
                'store.owner',
                'store.owner.favoriteUsers'
            ])
            ->first();

        if ($jobPosting) {
            return $jobPosting->store->owner->favoriteUsers;
        }

        throw new InputException(trans('response.not_found'));
    }


    /**
     * @param $jobList
     * @param $user
     * @return array
     */
    public static function appendInfoForJobs($jobList, $user)
    {
        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];
        $jobMasterData = JobHelper::getJobMasterData($needMasterData);
        $jobUserFavorite = self::getUserFavoriteJobIds($user);
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
            $job->is_favorite = $jobUserFavorite && !!in_array($job->id, $jobUserFavorite);

            $jobArr[$job->id] = $job;
        }//end foreach

        return $jobArr;
    }

    /**
     * @return array
     */
    public static function getOtherJobTypeIds()
    {
        return MJobType::query()->where('is_default', MJobType::NO_DEFAULT)
            ->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    public static function getOtherWorkTypeIds()
    {
        return MWorkType::query()->where('is_default', MWorkType::NO_DEFAULT)
            ->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    public static function getInterviewMethodName()
    {
        return MInterviewApproach::query()->pluck('name')->toArray();
    }

    /**
     * @return string
     */
    public function getTotalJobs()
    {
        $applicationIds = [];

        if ($this->user) {
            $applicationIds = JobService::getIdJobApplicationCancelOrReject($this->user);
        }

        $totalJobs = JobPosting::query()->released()->whereNotIn('id', $applicationIds)->count();

        return JobHelper::thousandNumberFormat($totalJobs);
    }

    /**
     * Check Time Date
     *
     * @param $id
     * @param null $application
     * @return array
     * @throws InputException
     */
    public function detailJobUserApplication($id, $application = null)
    {
        $jobPosting = $this->checkJobPosting($id);
        $storeOffTimes = StoreOffTime::query()->where('store_id', '=', $jobPosting->store_id)->first();
        $storeOffTimes = $storeOffTimes ? $storeOffTimes->off_times : [];
        $monthNow = now()->firstOfMonth()->format('Y-m-d');
        $monthDay = now()->addDays(config('date.max_day'))->firstOfMonth()->format('Y-m-d');


        $dataInterViewApproaches = MInterviewApproach::query()->get();
        $approach = [];

        foreach ($dataInterViewApproaches as $dataInterViewApproach) {
            $output = $dataInterViewApproach->name;

            if ($dataInterViewApproach->id == MInterviewApproach::STATUS_INTERVIEW_ONLINE) {
                $output .= sprintf('（%s）', config('application.interview_approach_online'));
            } elseif ($dataInterViewApproach->id == MInterviewApproach::STATUS_INTERVIEW_DIRECT) {
                $store = Store::query()
                    ->where('id', $jobPosting->store_id)
                    ->with(['province', 'provinceCity'])
                    ->first();
                $output .= sprintf('（%s %s%s%s%s）',
                    $store->postal_code ? sprintf('〒%s-%s', substr($store->postal_code, 0, 3), substr($store->postal_code, -4)) : null,
                    $store->province->name,
                    $store->provinceCity->name,
                    $store->address,
                    $store->building
                );
            }

            $approach[] = [
                'id' => $dataInterViewApproach->id,
                'name' => $output,
            ];
        }

        return [
            'time' => $this->resultDate(
                self::resultStoreOffTimes([$monthNow, $monthDay], $storeOffTimes),
                $application
            ),
            'approach' => $approach,
        ];
    }

    /**
     * @param $dataApplications
     * @return array
     */
    public static function resultApplication($dataApplications)
    {
        $data = [];
        foreach ($dataApplications as $application) {
            $data[explode(' ', $application->date)[0]][] = $application->hours;
        }

        return $data;
    }

    /**
     * Store off times
     *
     * @param $storeOffTimes
     * @param $key
     * @return mixed
     */
    public static function resultStoreOffTimes($key, $storeOffTimes = [])
    {
        $keys = array_map('strval', $key);

        return call_user_func_array('array_merge', array_values(array_intersect_key($storeOffTimes, array_flip($keys))));
    }

    /**
     * @param $storeOffTimes
     * @param null $application
     * @return array
     */
    public function resultDate($storeOffTimes, $application = null)
    {
        $dateStart = [];
        $i = 0;

        while ($i <= config('date.max_date')) {
            $dateCheck = now()->addDays($i)->format('Y-m-d');
            $timeChecks = [];
            $dataHours = preg_grep('/' . $dateCheck . '/i', $storeOffTimes);

            foreach ($dataHours as $dataHour) {
                $timeChecks[] = explode(' ', $dataHour)[1];
            }

            if ($application && $application->date == now()->format('Y-m-d 00:00:00')) {
                $times = $this->checkTime($dateCheck, $timeChecks, $application->hours);
            } else {
                $times = $this->checkTime($dateCheck, $timeChecks);
            }

            $dateStart[] = [
                'date' => $dateCheck,
                'date_format' => DateTimeHelper::formatDateDayOfWeekJa($dateCheck),
                'is_enable' => $times['is_enabled_date'],
                'times' => $times['times']
            ];

            $i++;
        }//end while

        return $dateStart;
    }

    /**
     * Time check
     *
     * @param $date
     * @param array $timeCoincides
     * @param null $hours
     * @return array
     */
    public function checkTime($date, $timeCoincides = [], $hours = null)
    {
        $data = [];
        $isEnabledDate = false;
        $currentHour = DateTimeHelper::getTime();
        $endTime = strtotime(date('Y-m-d' . config('date.time_max')));
        $checkTime = array_search($currentHour, config('date.time'));
        $checkTime = config('date.range_time') + ($checkTime ?? 0);

        foreach (config('date.time') as $key => $time) {
            if (in_array($time, $timeCoincides) ||
                ($date == now()->format('Y-m-d') && ($key < $checkTime || time() > $endTime))
                && $hours != $time
            ) {
                $data[] = [
                    'hours' => $time,
                    'is_enabled_time' => 0
                ];
            } else {
                $data[] = [
                    'hours' => $time,
                    'is_enabled_time' => 1
                ];
                $isEnabledDate = true;
            }
        }

        return [
            'is_enabled_date' => $isEnabledDate,
            'times' => $data,
        ];
    }

    /**
     * Check scheduling application
     *
     * @param $data
     * @return Builder|Model|object
     * @throws InputException
     * @throws ValidationException
     */
    public function checkSchedulingApplication($data)
    {
        $date = $data['date'];
        $hours = $data['hours'];
        $now = now()->format('Y-m-d');
        $user = $this->user;

        if ($date == $now && $this->checkTimeStore($hours)) {
            throw ValidationException::withMessages([
                'date' => trans('validation.ERR.037')
            ]);
        }

        $jobPosting = $this->checkJobPosting($data['id']);

        $application = Application::query()
            ->where('user_id', '=', $user->id)
            ->where('job_posting_id', '=', $jobPosting->id)
            ->exists();

        if ($application) {
            throw new InputException(trans('response.not_found'));
        }

        $month = Carbon::parse($data['date'])->firstOfMonth()->format('Y-m-d');
        $storeOffTimes = StoreOffTime::query()->where('store_id', '=', $jobPosting->store_id)->first();

        if ($storeOffTimes && isset($storeOffTimes->off_times[$month])) {
            $dataHours = preg_grep('/' . $date . '/i', $storeOffTimes->off_times[$month]);

            if (isset($dataHours[$date . ' ' . $hours])) {
                throw new InputException(trans('validation.ERR.036'));
            }
        }

        return $jobPosting;
    }

    /**
     * Store user application
     *
     * @param $data
     * @param $jobPosting
     * @return Builder|Model
     * @throws InputException
     */
    public function store($data, $jobPosting)
    {
        return Application::query()->create($this->makeSaveData($jobPosting, $data));
    }

    /**
     * Save make data
     *
     * @param $jobPosting
     * @param $data
     * @return array
     * @throws InputException
     */
    public function makeSaveData($jobPosting, $data)
    {
        $interviewApproaches = MInterviewApproach::query()->where('id', $data['interview_approaches_id'])->first();

        if (!$interviewApproaches) {
            throw new InputException('response.not_found');
        }

        return [
            'user_id' => $this->user->id,
            'job_posting_id' => $jobPosting->id,
            'store_id' => $jobPosting->store_id,
            'interview_status_id' => MInterviewStatus::STATUS_APPLYING,
            'interview_approach_id' => $interviewApproaches->id,
            'date' => $data['date'],
            'hours' => $data['hours'],
            'note' => $data['note'],
        ];
    }

    /**
     * @param $hours
     * @return bool
     */
    public function checkTimeStore($hours)
    {
        $checkTime = array_search(DateTimeHelper::getTime(), config('date.time'));
        $checkTime = config('date.range_time') + ($checkTime ? $checkTime : 0);

        foreach (config('date.time') as $key => $time) {
            if ($key < $checkTime && $hours == $time) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check job posting
     *
     * @param $jobPostingId
     * @return Builder|Model|object
     * @throws InputException
     */
    public function checkJobPosting($jobPostingId)
    {
        $jobPosting = JobPosting::query()
            ->released()
            ->with(['applications' => function($query) {
                $query->where('user_id', '!=', $this->user->id)
                    ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW]);
            }])
            ->where('id', '=', $jobPostingId)
            ->first();

        if (!$jobPosting) {
            throw new InputException(trans('response.not_found'));
        }

        return $jobPosting;
    }
}
