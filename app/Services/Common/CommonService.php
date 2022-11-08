<?php

namespace App\Services\Common;

use App\Helpers\CommonHelper;
use App\Models\MJobFeature;
use App\Models\MJobType;
use App\Models\MProvince;
use App\Models\MProvinceCity;
use App\Models\MStation;
use App\Models\MWorkType;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class CommonService extends Service
{
    const DEFAULT = 0;
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

    /**
     * @param $table
     * @return array
     */
    public static function getMasterDataFromTable($table)
    {
        if ($table == MJobFeature::getTableName()) {
            return MJobFeature::query()->with(['category'])->get();
        }

        $masterData = DB::table($table)->get();

        if ($table == MStation::getTableName()) {
            return CommonHelper::getMasterDataStations($masterData);
        }

        return CommonHelper::getMasterDataIdName($masterData);
    }

    /**
     * get other job type
     *
     * @return array
     */
    public static function getOtherTypeIds($table)
    {
        return DB::table($table)->where('is_default', self::DEFAULT)
            ->pluck('id')->toArray();
    }
}
