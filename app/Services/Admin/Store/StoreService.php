<?php

namespace App\Services\Admin\Store;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Application;
use App\Models\Image;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use App\Models\Store;
use App\Models\StoreOffTime;
use App\Models\UserJobDesiredMatch;
use App\Services\Common\FileService;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreService extends Service
{
    public function detail($id)
    {
        $store = Store::query()
            ->with([
                'owner',
                'storeBanner',
                'provinceCity',
                'provinceCity.province',
            ])
            ->where('id', $id)
            ->withTrashed()
            ->get();

        if (!$store) {
            return null;
        }

        return self::appendMasterDataForStore($store);
    }

    /**
     * get name master data JobType
     *
     * @param $stores
     * @return array
     */
    public static function appendMasterDataForStore($stores)
    {
        $masterData = CommonHelper::getMasterDataJobPostingTypes();
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
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function store($data)
    {
        $dataImage = array(FileHelper::fullPathNotDomain($data['url']));

        try {
            DB::beginTransaction();

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
     * @param $id
     * @return mixed
     * @throws InputException
     */
    public function update($data, $id)
    {
        $store = Store::find($id);
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
            throw new Exception(trans('validation.EXC.001'));
        }
    }

    public function delete($id)
    {
        $store = Store::find($id);

        if (!$store) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            $store->contacts()?->delete();
            $store->images()?->delete();
            StoreOffTime::query()->where('store_id', '=', $store->id)->delete();

            $jobHasApplicationRejectAccept = $store->jobs()?->whereHas('applications', function ($query) {
                $query->whereNotIn('interview_status_id', [MInterviewStatus::STATUS_REJECTED, MInterviewStatus::STATUS_ACCEPTED]);
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

            if ($store->jobs()) {
                UserJobDesiredMatch::whereIn('job_id', $store->jobs()->pluck('id')->toArray())->delete();
            }

            $store->feedbacks()?->delete();
            $store->jobs()?->delete();
            $store->delete();

            if ($jobHasApplicationRejectAccept->count()) {
                foreach ($jobHasApplicationRejectAccept as $job) {
                    foreach ($job->applications as $application) {
                        Notification::create([
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
                        ]);
                    }
                }//end foreach
            }//end if

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);
            throw new Exception(trans('validation.EXC.001'));
        }//end try
    }

    /**
     * @return array
     */
    public function all()
    {
        $stores = Store::query()->get();
        $result = [];

        foreach ($stores as $store) {
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
}
