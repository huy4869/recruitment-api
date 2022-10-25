<?php

namespace App\Services\Recruiter;

use App\Helpers\DateTimeHelper;
use App\Helpers\JobHelper;
use App\Models\Store;
use App\Services\Service;
use App\Services\User\Job\JobService;

class StoreService extends Service
{
    /**
     * List store
     *
     * @return array
     */
    public function list()
    {
        $rec = $this->user;
        $stores = Store::query()->where('user_id', $rec->id)->orderByDesc('created_at')->get();
        $masterData = JobService::getMasterDataJobPostingTypes();
        $data = [];

        foreach ($stores as $store) {
            $data[] = self::addFormatStoreJsonData($store, $masterData);
        }

        return $data;
    }

    /**
     * @param $store
     * @param $masterData
     * @return array
     */
    public static function addFormatStoreJsonData($store, $masterData)
    {
        $specializes = JobHelper::getTypeName($store->specialize_ids, $masterData);

        return [
            'created_at' => DateTimeHelper::formatDateTimeJa($store->created_at),
            'store_name' => $store->name,
            'address' => $store->full_name_address,
            'tel' => $store->tel,
            'specialize' => $specializes,
            'recruiter_name' => $store->rescuiter_name,
        ];
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
