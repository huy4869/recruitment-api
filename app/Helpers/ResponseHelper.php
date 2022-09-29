<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public const STATUS_CODE_SUCCESS = 200;
    public const STATUS_CODE_UNAUTHORIZED = 401;
    public const STATUS_CODE_FORBIDDEN = 403;
    public const STATUS_CODE_BAD_REQUEST = 400;
    public const STATUS_CODE_NOTFOUND = 404;
    public const STATUS_CODE_VALIDATE_ERROR = 422;
    public const STATUS_CODE_SERVER_ERROR = 500;

    /**
     * Send Response
     *
     * @param $code
     * @param $message
     * @param null $data
     * @return JsonResponse
     */
    public static function sendResponse($code, $message, $data = null)
    {
        return response()->json([
            'status_code' => $code,
            'messages' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Send Json Response
     *
     * @param $data
     * @return JsonResponse
     */
    public static function sendJsonResponse($data)
    {
        return response()->json($data);
    }
}
