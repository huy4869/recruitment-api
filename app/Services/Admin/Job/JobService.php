<?php

namespace App\Services\Admin\Job;

use App\Helpers\FileHelper;
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
}
