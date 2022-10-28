<?php

namespace App\Services\Common;

use App\Models\MProvince;
use App\Models\MProvinceCity;
use App\Services\Service;

class CommonService extends Service
{
    /**
     * @return array
     */
    public static function getListIdsLocationMasterData()
    {
        $provinceIds = MProvince::query()->pluck('id')->toArray();
        $provinceCityIds = MProvinceCity::query()->pluck('id')->toArray();

        return [
            'provinceIds' => $provinceIds,
            'provinceCityIds' => $provinceCityIds,
        ];
    }

    /**
     * @return array
     */
    public static function getMasterDataProvinceCities()
    {
        return MProvinceCity::query()->with([
            'province',
            'province.provinceDistrict'
        ])->get()->toArray();
    }
}
