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

    /**
     * @param $R1
     * @param $G1
     * @param $B1
     * @param $R2
     * @param $G2
     * @param $B2
     * @return float
     */
    public static function lumdiff($R1,$G1,$B1,$R2,$G2,$B2){
        $L1 = 0.2126 * pow($R1/255, 2.2) +
            0.7152 * pow($G1/255, 2.2) +
            0.0722 * pow($B1/255, 2.2);

        $L2 = 0.2126 * pow($R2/255, 2.2) +
            0.7152 * pow($G2/255, 2.2) +
            0.0722 * pow($B2/255, 2.2);

        if($L1 > $L2){
            return ($L1+0.05) / ($L2+0.05);
        }else{
            return ($L2+0.05) / ($L1+0.05);
        }
    }

    public static function generateColor($hash) {
        $brightness = 0;
        $shift = 0;

        while ($brightness < 5 && $shift < 26) {
            $color = [
                hexdec(substr($hash, $shift, 2)), //r
                hexdec(substr($hash, $shift+2, 2)), // g
                hexdec(substr($hash, $shift+4, 2))  // b
            ];
            $brightness = self::lumdiff($color[0], $color[1], $color[2], 255,255,255);
            $shift++;
        }

        return sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2]);
    }

    /**
     * @param $value
     * @return string
     */
    public static function makeRgbFromValue($value)
    {
        $hash = md5($value);
        $rand = rand(0, 9);
        $color = self::generateColor($hash);
        $firstColor = substr($color, 1, 1);
        $threeColor = substr($color, 3, 1);

        if (!is_int($firstColor)) {
            $color = str_replace($firstColor, $rand, $color);
        }

        if (!is_int($threeColor)) {
            $color = str_replace($threeColor, $rand, $color);
        }

        return $color;
    }
}
