<?php

namespace App\Helpers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpHelper
{
    /**
     * Get method
     *
     * @param $url
     * @param array $params
     * @return array|Translator|string|null
     */
    public static function get($url, $params = [])
    {
        $response = Http::get($url, $params);
        if ($response->successful()) {
            return json_decode($response->body(), true);
        }

        return null;
    }

    /**
     * Post method
     *
     * @param $url
     * @param array $params
     * @return mixed|null
     */
    public static function post($url, $params = [])
    {
        $response = Http::post($url, $params);
        if ($response->successful()) {
            return json_decode($response->body(), true);
        }

        return null;
    }

    /**
     * Post method
     *
     * @param $url
     * @param array $params
     * @param array $headers
     * @return mixed|null
     */
    public static function postAsForm($url, $params = [], $headers = [])
    {
        $response = Http::withHeaders($headers)->asForm()->post($url, $params);
        if ($response->successful()) {
            return json_decode($response->body(), true);
        }

        Log::info("[Http postAsForm]", [json_decode($response->body(), true)]);

        return null;
    }
}
