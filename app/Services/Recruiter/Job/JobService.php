<?php

namespace App\Services\Recruiter\Job;

use App\Exceptions\InputException;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Image;
use App\Models\JobPosting;
use App\Models\MJobStatus;
use App\Models\MSalaryType;
use App\Services\Common\FileService;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobService extends Service
{
    const MAX_DETAIL_IMAGE = 3;
    const MAX_STATIONS = 3;

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function create($data)
    {
        if (count($data['job_thumbnails']) > self::MAX_DETAIL_IMAGE
            || count($data['station_ids']) > self::MAX_STATIONS
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
        if (count($data['job_thumbnails']) > self::MAX_DETAIL_IMAGE
            || count($data['station_ids']) > self::MAX_STATIONS
        ) {
            throw new InputException(trans('response.invalid'));
        }

        $recruiter = $this->user;
        $job = JobPosting::query()->where('id', $id)->with(['store'])->first();

        if ($job->store->user_id != $recruiter->id) {
            throw new InputException(trans('response.not_found'));
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
            'salaryType',
        ])
        ->first();

        if ($job->store->user_id != $recruiter->id) {
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

        return $job;
    }
}
