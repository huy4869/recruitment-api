<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Helpers\StringHelper;
use App\Models\Application;
use App\Models\Image;
use App\Models\JobPosting;
use App\Models\MInterviewStatus;
use App\Models\MJobType;
use App\Models\Notification;
use App\Models\Store;
use App\Models\StoreOffTime;
use App\Services\Common\FileService;
use App\Services\Service;
use App\Services\User\Job\JobService;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreService extends Service
{
    /**
     * get name master data JobType
     *
     * @param $stores
     * @return array
     */
    public static function appendMasterDataForStore($stores)
    {
        $masterData = JobService::getMasterDataJobPostingTypes();
        $data = [];

        foreach ($stores as $store) {
            $store->specialize_ids = JobHelper::getTypeName(
                $store->specialize_ids,
                $masterData
            );
            $data[$store->id] = $store;
        }

        return $data;
    }

    /**
     * delete store
     *
     * @param $id
     * @return bool
     * @throws InputException
     * @throws Exception
     */
    public function delete($id)
    {
        $store = Store::where([
            ['id', $id],
            ['user_id', $this->user->id]
        ])->first();

        if (!$store) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            $store->contacts()?->delete();
            $store->images()?->delete();
            StoreOffTime::query()->where('store_id', '=', $store->id)->delete();

            $jobHasApplicationNotReject = $store->jobs()?->whereHas('applications', function ($query) {
                $query->whereNot('interview_status_id', MInterviewStatus::STATUS_REJECTED);
            })->with([
                'store',
                'applications'
            ])->get();

            $store->applications()?->whereIn('interview_status_id', [
                MInterviewStatus::STATUS_APPLYING,
                MInterviewStatus::STATUS_WAITING_INTERVIEW,
                MInterviewStatus::STATUS_WAITING_RESULT
            ])->update([
                'interview_status_id' => MInterviewStatus::STATUS_REJECTED
            ]);

            $store->jobImages()?->delete();
            $store->feedbacks()?->delete();
            $store->jobs()?->delete();
            $store->delete();

            $userNotifyData = [];

            if ($jobHasApplicationNotReject->count()) {
                foreach ($jobHasApplicationNotReject as $job) {
                    foreach ($job->applications as $application) {
                        $userNotifyData[] = [
                            'user_id' => $application->user_id,
                            'notice_type_id' => Notification::TYPE_DELETE_STORE,
                            'noti_object_ids' => json_encode([
                                'job_posting_id' => $application->job_posting_id,
                                'application_id' => $application->id,
                                'user_id' => $this->user->id,
                            ]),
                            'title' => trans('notification.N012.title'),
                            'content' => trans('notification.N012.content', [
                                'store_name' => $job->store->name,
                                'job_name' => $job->name,
                            ]),
                            'created_at' => now(),
                        ];
                    }
                }//end foreach
            }//end if

            Notification::insert($userNotifyData);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }

    /**
     * @return array
     */
    public function getAllStoreNameByOwner()
    {
        $recruiter = $this->user;

        if (!$recruiter) {
            return [];
        }

        $recruiterStores = $recruiter->stores;
        $result = [];

        foreach ($recruiterStores as $store) {
            $result[] = [
                'id' => $store->id,
                'hex_color' => $store->hex_color,
                'name' => $store->name,
                'province_id' => $store->province_id,
                'province_city_id' => $store->province_city_id
            ];
        }

        return $result;
    }

    public function detail($store_id)
    {
        $store = Store::with([
                'storeBanner',
                'provinceCity',
                'provinceCity.province',
            ])
            ->where([
                ['user_id', $this->user->id],
                ['id', $store_id]
            ])
            ->withTrashed()
            ->get();

        if (!$store) {
            return null;
        }

        return self::appendMasterDataForStore($store);
    }

    /**
     * create
     *
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function store($data)
    {
        $dataImage = array(FileHelper::fullPathNotDomain($data['url']));

        try {
            DB::beginTransaction();
            $data['user_id'] = $this->user->id;
            $data['created_by'] = $this->user->id;
            $data['name'] = $data['store_name'];
            $data['founded_year'] = str_replace('/', '', $data['founded_year']);
            $data['hex_color'] = CommonHelper::makeRgbFromValue(rand(100000000, 999999999));
            $data['application_tel'] = isset($data['application_tel']) && $data['application_tel'] ? str_replace('-', '', $data['application_tel']) : '';
            $data['tel'] = str_replace('-', '', $data['tel']);
            $store = Store::create($data);
            FileService::getInstance()->updateImageable($store, $dataImage, [Image::STORE_BANNER]);

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function update($data, $id)
    {
        $store = $this->user->stores()->find($id);
        $dataImage = array(FileHelper::fullPathNotDomain($data['url']));

        if (!$store) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();
            $data['name'] = $data['store_name'];
            $data['founded_year'] = str_replace('/', '', $data['founded_year']);
            $data['application_tel'] = isset($data['application_tel']) && $data['application_tel'] ? str_replace('-', '', $data['application_tel']) : '';
            $data['tel'] = str_replace('-', '', $data['tel']);
            FileService::getInstance()->updateImageable($store, $dataImage, [Image::STORE_BANNER]);
            $store->update($data);
            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);
            throw new Exception($e->getMessage());
        }
    }
}
