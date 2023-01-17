<?php

namespace App\Services\Admin\Job;

use App\Exceptions\InputException;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Application;
use App\Models\Image;
use App\Models\JobPosting;
use App\Models\MInterviewStatus;
use App\Models\MJobStatus;
use App\Models\MSalaryType;
use App\Models\Notification;
use App\Models\Store;
use App\Models\UserJobDesiredMatch;
use App\Services\Common\FileService;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobService extends Service
{
    const QUANTITY_CHUNK = 500;

    /**
     * @param $data
     * @return Builder|Model
     * @throws Exception
     */
    public function create($data)
    {
        $admin = $this->user;
        $data['created_by'] = $admin->id;

        if (isset($data['working_days'])) {
            $data['working_days'] = collect($data['working_days'])->filter(function ($item) {
                return $item;
            });
        }

        if ($data['job_status_id'] == JobPosting::STATUS_RELEASE) {
            $data['released_at'] = now();
        }

        $dataImage = $this->makeSaveDataImage($data);

        if (isset($data['job_banner'])) {
            unset($data['job_banner']);
        }

        if (isset($data['job_thumbnails'])) {
            unset($data['job_thumbnails']);
        }

        try {
            DB::beginTransaction();

            $job = JobPosting::query()->create($data);

            FileService::getInstance()->updateImageable($job, $dataImage, [
                Image::JOB_BANNER,
                Image::JOB_DETAIL
            ]);

            DB::commit();
            return $job;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new Exception($exception->getMessage());
        }//end try
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

        if (isset($data['job_thumbnails'])) {
            foreach ($data['job_thumbnails'] as $image) {
                $dataUrl[] = FileHelper::fullPathNotDomain($image);
            }
        }

        if (isset($data['job_banner'])) {
            $dataUrl = array_merge([FileHelper::fullPathNotDomain($data['job_banner'])], $dataUrl);
        }

        return $dataUrl;
    }

    /**
     * @return array
     */
    public static function getJobStatusIdsNotEnd()
    {
        return MJobStatus::query()->whereNot('id', JobPosting::STATUS_END)->pluck('id')->toArray();
    }

    /**
     * @param $id
     * @return array
     * @throws InputException
     */
    public function getDetail($id)
    {
        $job = JobPosting::query()->where('id', $id)->with([
            'storeTrashed',
            'storeTrashed.owner' => function ($q) {
                $q->withTrashed();
            },
            'bannerImage',
            'detailImages',
            'province',
            'provinceCity',
            'province.provinceDistrict',
            'salaryType',
        ])
            ->withTrashed()
            ->first();

        if (!$job) {
            return null;
        }

        return self::getJobInfoForDetailJob($job);
    }

    /**
     * @param $id
     * @param $data
     * @return Builder|Model|object
     * @throws InputException
     * @throws Exception
     */
    public function update($id, $data)
    {
        $job = JobPosting::query()->where('id', $id)->with(['store'])->first();

        if (isset($data['working_days'])) {
            $data['working_days'] = collect($data['working_days'])->filter(function ($item) {
                return $item;
            });
        }

        if (!$job) {
            throw new InputException(trans('response.not_found'));
        }

        if ($job->job_status_id != JobPosting::STATUS_DRAFT) {
            try {
                DB::beginTransaction();

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
                                'job_id' => $job->id,
                            ]),
                            'title' => trans('notification.N017.title'),
                            'content' => trans('notification.N017.content', [
                                'store_name' => $job->store->name,
                                'job_name' => $job->name
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $applications->update(['interview_status_id' => MInterviewStatus::STATUS_REJECTED]);
                    collect($notifications)->chunk(self::QUANTITY_CHUNK)->each(function ($notifications) {
                        Notification::query()->insert($notifications->toArray());
                    });
                }//end if

                $updateData = ['job_status_id' => $data['job_status_id']];

                if (
                    $job->job_status_id != JobPosting::STATUS_RELEASE &&
                    $data['job_status_id'] == JobPosting::STATUS_RELEASE
                ) {
                    $updateData['released_at'] = now();
                }

                $job->update($updateData);

                DB::commit();

                return $job;
            } catch (Exception $exception) {
                DB::rollBack();
                Log::error($exception->getMessage(), [$exception]);
                throw new Exception($exception->getMessage());
            }//end try
        }

        $dataImage = $this->makeSaveDataImage($data);

        if (isset($data['job_banner'])) {
            unset($data['job_banner']);
        }

        if (isset($data['job_thumbnails'])) {
            unset($data['job_thumbnails']);
        }

        try {
            DB::beginTransaction();

            FileService::getInstance()->updateImageable($job, $dataImage, [
                Image::JOB_BANNER,
                Image::JOB_DETAIL
            ]);

            if (
                $job->job_status_id != JobPosting::STATUS_RELEASE &&
                $data['job_status_id'] == JobPosting::STATUS_RELEASE
            ) {
                $data['released_at'] = now();
            }

            $job->update($data);

            DB::commit();
            return $job;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new Exception($exception->getMessage());
        }//end try
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
            $job->experience_ids,
            $jobMasterData['masterJobExperiences']
        );
        $job->feature_types = JobHelper::getFeatureCategoryName(
            $job->feature_ids,
            $jobMasterData['masterJobFeatures']
        );
        $job->working_days = JobHelper::getWorkingDays(
            $job->working_days,
            config('date.day_of_week_ja_fe')
        );
        $job->stations = JobHelper::getStations(
            $job->station_ids,
            $jobMasterData['masterStations']
        );

        return $job;
    }

    /**
     * @param $id
     * @return bool
     * @throws InputException
     * @throws Exception
     */
    public function delete($id)
    {
        $job = JobPosting::query()->where('id', $id)->with('storeTrashed')->withTrashed()->first();
        $notifications = [];

        if (!$job) {
            throw new InputException(trans('response.invalid'));
        }

        if(!is_null($job->deleted_at)) {
            throw new InputException(trans('response.deleted_job'));
        }

        $applications = $job->applications()?->whereNotIn('interview_status_id', [
            MInterviewStatus::STATUS_REJECTED,
            MInterviewStatus::STATUS_CANCELED,
            MInterviewStatus::STATUS_ACCEPTED
        ]);

        foreach ($applications->get() as $application) {
            $notifications[] = [
                'user_id' => $application->user_id,
                'notice_type_id' => Notification::TYPE_DELETE_JOB,
                'noti_object_ids' => json_encode([
                    'store_id' => $application->store_id,
                    'application_id' => $application->id,
                    'user_id' => $this->user->id,
                    'job_id' => $job->id
                ]),
                'title' => trans('notification.N011.title'),
                'content' => trans('notification.N011.content', [
                    'store_name' => $job->storeTrashed->name,
                    'job_name' => $job->name
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        try {
            DB::beginTransaction();

            $applications->update([
                'interview_status_id' => MInterviewStatus::STATUS_REJECTED
            ]);

            if ($notifications) {
                collect($notifications)->chunk(self::QUANTITY_CHUNK)->each(function ($data) {
                    Notification::query()->insert($data->toArray());
                });
            }

            $job->feedbacks()?->delete();
            $job->userJobDesiredMatch()?->delete();
            $job->delete();

            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new Exception($exception->getMessage());
        }
    }

    public static function getStatusJob()
    {
        $jobStatus = MJobStatus::query()->get();
        $dataStatus = [];

        foreach ($jobStatus as $status) {
            $dataStatus[] = [
                'id' => $status->id,
                'name' => $status->name,
            ];
        }

        return $dataStatus;
    }
}
