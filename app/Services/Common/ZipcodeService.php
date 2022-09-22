<?php

namespace App\Services\Common;

use App\Helpers\HttpHelper;
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

        return ($data && isset($data['results'])) ? $data['results'] : [];
    }
}
