<?php

namespace App\Services\Admin\Store;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Models\Image;
use App\Models\Store;
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
                'storeBanner',
                'provinceCity',
                'provinceCity.province',
            ])
            ->where('id', $id)
            ->get();

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