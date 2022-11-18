<?php

namespace App\Services\Common;

use App\Helpers\HttpHelper;
use App\Models\MProvince;
use App\Models\MProvinceCity;
use App\Models\MProvinceDistrict;
use App\Services\Service;
use Illuminate\Contracts\Translation\Translator;

class ZipcodeService extends Service
{
    /**
     * Base url
     *
     * @var string
     */
    protected $baseUrl = 'https://zipcloud.ibsnet.co.jp/api/search';

    /**
     * Get zipcode
     *
     * @param $zipcode
     * @return array|Translator|string|null
     */
    public function getZipcode($zipcode)
    {
        $params = [
            'zipcode' => $zipcode,
        ];

        $data = HttpHelper::get($this->baseUrl, $params);

        if (!$data || !isset($data['results'])) {
           return [];
        }

        $dataResult = array_merge(...$data['results']);

        $provinceId = MProvince::query()->where('name', '=', $dataResult['address1'])->first()->id ?? null;
        $provinceCityId = MProvinceCity::query()
                ->where('province_id', '=', $provinceId)
                ->where('name', '=', $dataResult['address2'])
                ->first()->id ?? null;

        return [
            'province_id' => $provinceId,
            'province_city_id' => $provinceCityId,
            'address' => $dataResult['address3'],
            'zipcode' => $dataResult['zipcode'],
            'prefcode' => $dataResult['prefcode'],
            'province_name' => $dataResult['address1'],
            'province_city_name' => $dataResult['address2'],
        ];
    }
}
