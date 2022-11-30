<?php

namespace App\Services\Recruiter\Job;

use App\Exceptions\InputException;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Application;
use App\Models\Image;
use App\Models\JobPosting;
use App\Models\MInterviewStatus;
use App\Models\MJobStatus;
use App\Models\MJobType;
use App\Models\MSalaryType;
use App\Models\MWorkType;
use App\Models\Notification;
use App\Models\UserJobDesiredMatch;
use App\Services\Common\FileService;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobService extends Service
{
    const MAX_DETAIL_IMAGE = 3;
    const MAX_STATIONS = 3;
    const QUANTITY_CHUNK = 500;

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function create($data)
    {
        if (
            (isset($data['job_thumbnails']) && count($data['job_thumbnails']) > self::MAX_DETAIL_IMAGE)
            || (isset($data['station_ids']) && count($data['station_ids']) > self::MAX_STATIONS)
        ) {
            throw new InputException(trans('response.invalid'));
        }

        $recruiter = $this->user;
        $data['created_by'] = $recruiter->id;

        if ($data['job_status_id'] == JobPosting::STATUS_RELEASE) {
            $data['released_at'] = now();
        }

        $dataImage = $this->makeSaveDataImage($data);
        unset($data['job_banner']);
        unset($data['job_thumbnails']);

        try {
            DB::beginTransaction();

            FileService::getInstance()->updateImageable(new JobPosting, $dataImage, [
                Image::JOB_BANNER,
                Image::JOB_DETAIL
            ]);

            JobPosting::create($data);

            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new Exception($exception->getMessage());
        }//end try
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function update($id, $data)
    {
        if (
            (isset($data['job_thumbnails']) && count($data['job_thumbnails']) > self::MAX_DETAIL_IMAGE)
            || (isset($data['station_ids']) && count($data['station_ids']) > self::MAX_STATIONS)
        ) {
            throw new InputException(trans('response.invalid'));
        }

        $recruiter = $this->user;
        $job = JobPosting::query()->where('id', $id)->with(['store'])->first();

        if ($job->job_status_id == JobPosting::STATUS_END) {
            throw new InputException(trans('validation.ERR.999'));
        }

        if ($job->store->user_id != $recruiter->id) {
            throw new InputException(trans('response.not_found'));
        }

        if ($data['job_status_id'] == JobPosting::STATUS_DRAFT && $job->job_status_id == JobPosting::STATUS_RELEASE) {
            $applications = Application::query()
                ->where('job_posting_id', '=', $job->id)
                ->whereIn('interview_status_id', [
                    MInterviewStatus::STATUS_APPLYING,
                    MInterviewStatus::STATUS_WAITING_INTERVIEW,
                    MInterviewStatus::STATUS_WAITING_RESULT
                ])
                ->get();

            if (count($applications)) {
                throw new InputException(trans('validation.ERR.050'));
            }
        }

        $dataImage = $this->makeSaveDataImage($data);
        unset($data['job_banner']);
        unset($data['job_thumbnails']);

        try {
            DB::beginTransaction();

            FileService::getInstance()->updateImageable($job, $dataImage, [
                Image::JOB_BANNER,
                Image::JOB_DETAIL
            ]);

            if ($data['job_status_id'] == JobPosting::STATUS_END) {
                $applications = Application::query()
                    ->where('job_posting_id', '=', $job->id)
                    ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW, MInterviewStatus::STATUS_WAITING_RESULT]);
                UserJobDesiredMatch::query()->where('job_id', '=', $job->id)->delete();
                $notifications = [];

                foreach ($applications->get() as $application) {
                    $notifications[] = [
                        'user_id' => $application->user_id,
                        'notice_type_id' => Notification::TYPE_DELETE_JOB,
                        'noti_object_ids' => json_encode([
                            'store_id' => $application->store_id,
                            'application_id' => $application->id,
                            'user_id' => $this->user->id,
                        ]),
                        'title' => trans('notification.N011.title'),
                        'content' => trans('notification.N011.content', ['job_id' => $job->name]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $applications->update(['interview_status_id' => MInterviewStatus::STATUS_REJECTED]);
                collect($notifications)->chunk(self::QUANTITY_CHUNK)->each(function ($notifications) {
                    Notification::query()->insert($notifications->toArray());
                });
            }//end if

            $job->update($data);

            DB::commit();
            return $job->job_status_id;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new Exception($exception->getMessage());
        }//end try
    }

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

            $job->applications()?->whereIn('interview_status_id', [
                MInterviewStatus::STATUS_APPLYING,
                MInterviewStatus::STATUS_WAITING_INTERVIEW,
                MInterviewStatus::STATUS_WAITING_RESULT
            ])->update([
                'interview_status_id' => MInterviewStatus::STATUS_REJECTED
            ]);
            $job->feedbacks()?->delete();
            $job->userJobDesiredMatch()?->delete();
            $job->images()?->delete();
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
     * Make Save data images
     *
     * @param $data
     * @return array
     */
    private function makeSaveDataImage($data)
    {
        $dataUrl = [];

        foreach ($data['job_thumbnails'] as $image) {
            $dataUrl[] = FileHelper::fullPathNotDomain($image);
        }

        return array_merge([FileHelper::fullPathNotDomain($data['job_banner'])], $dataUrl);
    }

    /**
     * @param $recruiter
     * @return mixed
     */
    public static function getStoreIdsAccordingToRecruiter($recruiter)
    {
        return $recruiter->stores()->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    public static function getSalaryTypeIds()
    {
        return MSalaryType::query()->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    public static function getJobStatusIdsNotEnd()
    {
        return MJobStatus::query()->whereNot('id', JobPosting::STATUS_END)
            ->pluck('id')
            ->toArray();
    }

    /**
     * @return array
     */
    public static function getJobStatusIds()
    {
        return MJobStatus::query()->whereNot('id', JobPosting::STATUS_END)
            ->pluck('id')
            ->toArray();
    }

    /**
     * @return array
     */
    public function getAllJobNameByOwner()
    {
        $recruiter = auth()->user();

        if (!$recruiter) {
            return [];
        }

        $recruiterStores = $recruiter->stores()->with(['jobs'])->get();
        $result = [];

        foreach ($recruiterStores as $store) {
            foreach ($store->jobs as $job) {
                $result[] = [
                    'id' => $job->id,
                    'name' => $job->name,
                ];
            }
        }

        return $result;
    }

    /**
     * @param $id
     * @return array
     * @throws InputException
     */
    public function getDetail($id)
    {
        $recruiter = $this->user;
        $job = JobPosting::query()->where('id', $id)->with([
            'store',
            'bannerImage',
            'detailImages',
            'province',
            'province.provinceDistrict',
            'provinceCity',
            'salaryType',
        ])
        ->first();

        if (!$job || $job->store->user_id != $recruiter->id) {
            throw new InputException(trans('response.not_found'));
        }

        return self::getJobInfoForDetailJob($job);
    }

    /**
     * @param $jobList
     * @return array
     */
    public static function getJobInfoForListJob($jobList)
    {
        $needMasterData = [
            MJobType::getTableName(),
            MWorkType::getTableName(),
        ];
        $jobMasterData = JobHelper::getJobMasterData($needMasterData);
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

    /**
     * @param $job
     * @return array
     */
    public static function getJobInfoForDetailJob($job)
    {
        $jobMasterData = JobHelper::getJobMasterData();

        $job->job_types = JobHelper::getTypeName(
            $job->job_type_ids,
            $jobMasterData['masterJobTypes']
        );
        $job->work_types = JobHelper::getTypeName(
            $job->work_type_ids,
            $jobMasterData['masterWorkTypes']
        );
        $job->genders = JobHelper::getTypeName(
            $job->gender_ids,
            $jobMasterData['masterGenders']
        );
        $job->expericence_types = JobHelper::getTypeName(
            $job->work_type_ids,
            $jobMasterData['masterJobExperiences']
        );
        $job->feature_types = JobHelper::getFeatureCategoryName(
            $job->feature_ids,
            $jobMasterData['masterJobFeatures']
        );
        $job->stations = JobHelper::getStations(
            $job->station_ids,
            $jobMasterData['masterStations']
        );
        $job->working_days = JobHelper::getWorkingDays(
            $job->working_days,
            config('date.day_of_week_ja_fe')
        );

        return $job;
    }

    public static function getStatusJob($idStatus)
    {
        $jobStatus = MJobStatus::query();

        if (in_array($idStatus, [JobPosting::STATUS_DRAFT, JobPosting::STATUS_RELEASE])) {
            $dataJob = $jobStatus->get();
            $dataStatus = [];

            foreach ($dataJob as $job) {
                $dataStatus[] = [
                    'id' => $job->id,
                    'name' => $job->name,
                ];
            }
        } else {
            $job = $jobStatus->where('id', JobPosting::STATUS_END)->first();
            $dataStatus[] = [
                'id' => $job->id,
                'name' => $job->name,
            ];
        }

        return $dataStatus;
    }
}
