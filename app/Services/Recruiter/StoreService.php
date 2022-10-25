<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\JobHelper;
use App\Models\Store;
use App\Services\Service;
use App\Services\User\Job\JobService;

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
}
