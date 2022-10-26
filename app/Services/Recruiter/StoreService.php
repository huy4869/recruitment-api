<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Image;
use App\Models\JobPosting;
use App\Models\MJobType;
use App\Models\Store;
use App\Services\Common\FileService;
use App\Services\Service;
use App\Services\User\Job\JobService;
use Exception;
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
    public static function getNameJobTypes($stores)
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
     */
    public function delete($id)
    {
        $result = Store::where([['id', $id], ['user_id', $this->user->id]])->delete();

        if ($result) {
            return true;
        }

        throw new InputException(trans('response.not_found'));
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
                'name' => $store->name,
            ];
        }

        return $result;
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
