<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Requests\User\ZipcodeRequest;
use App\Services\Common\ZipcodeService;
use Illuminate\Http\JsonResponse;

class ZipcodeController extends BaseController
{
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
