<?php

namespace App\Helpers;

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
}
