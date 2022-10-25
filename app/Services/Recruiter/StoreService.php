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
            ->get();

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

            $store = Store::create($this->storeData($data));
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
     * data
     *
     * @param $data
     * @return array
     */
    private function storeData($data)
    {
        return [
            'name' => $data['name'],
            'website' => $data['website'],
            'tel' => $data['tel'],
            'postal_code' => $data['postal_code'],
            'province_id' => $data['province_id'],
            'province_city_id' => $data['province_city_id'],
            'city' => $data['city'],
            'address' => $data['address'],
            'manager_name' => $data['manager_name'],
            'employee_quantity' => $data['employee_quantity'],
            'founded_year' => str_replace('/', '', $data['founded_year']),
            'business_segment' => $data['business_segment'],
            'specialize_ids' => $data['specialize_ids'],
            'store_features' => $data['store_features'],
            'recruiter_name' => $data['recruiter_name'],
            'user_id' => $this->user->id,
            'created_by' => $this->user->id,
        ];
    }
}
