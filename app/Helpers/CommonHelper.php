<?php

namespace App\Helpers;

use App\Models\MJobType;
use App\Models\MWorkType;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommonHelper
{
    /**
     * Get master data id/name
     *
     * @param $queryModel
     * @return array
     */
    public static function getMasterDataIdName($queryModel)
    {
        $result = [];

        foreach ($queryModel as $item) {
            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }

        return $result;
    }

    /**
     * @param $queryModel
     * @return array
     */
    public static function getMasterDataStations($queryModel)
    {
        $result = [];

        foreach ($queryModel as $item) {
            $result[] = [
                'id' => $item->id,
                'province_name' => $item->province_name,
                'railway_name' => $item->railway_name,
                'station_name' => $item->station_name,
            ];
        }

        return $result;
    }

    public static function getMasterDataJobPostingTypes()
    {
        $jobTypes = MJobType::all();

        return self::getMasterDataIdName($jobTypes);
    }
}
