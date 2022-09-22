<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ZipcodeRequest;
use App\Services\Common\ZipcodeService;
use Illuminate\Http\JsonResponse;

class ZipcodeController extends BaseController
{
    /**
     * ZipCodeController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware());
    }

    /**
     * Get zipcode
     *
     * @param ZipcodeRequest $request
     * @return JsonResponse
     */
    public function index(ZipcodeRequest $request)
    {
        $data = ZipcodeService::getInstance()->getZipcode($request->get('zipcode'));

        return $this->sendSuccessResponse($data);
    }
}
