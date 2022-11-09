<?php

namespace App\Services\Admin\Job;

use App\Exceptions\InputException;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Image;
use App\Models\JobPosting;
use App\Models\MJobStatus;
use App\Models\MSalaryType;
use App\Models\Store;
use App\Services\Common\FileService;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobService extends Service
{
    /**
     * @param $data
     * @return Builder|Model
     * @throws Exception
     */
    public function create($data)
    {
        $admin = $this->user;
        $data['created_by'] = $admin->id;

        if ($data['job_status_id'] == JobPosting::STATUS_RELEASE) {
            $data['released_at'] = now();
        }

        $dataImage = $this->makeSaveDataImage($data);
        unset($data['job_banner']);
        unset($data['job_thumbnails']);

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

        foreach ($data['job_thumbnails'] as $image) {
            $dataUrl[] = FileHelper::fullPathNotDomain($image);
        }

        return array_merge([FileHelper::fullPathNotDomain($data['job_banner'])], $dataUrl);
    }

    /**
     * @return mixed
     */
    public static function getStoreIdsAccordingToAdmin()
    {
        return Store::query()->get()->pluck('id')->toArray();
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
            'store',
            'bannerImage',
            'detailImages',
            'province',
            'provinceCity',
            'province.provinceDistrict',
            'salaryType',
        ])
            ->first();

        if (!$job) {
            throw new InputException(trans('response.not_found'));
        }

        return self::getJobInfoForDetailJob($job);
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
