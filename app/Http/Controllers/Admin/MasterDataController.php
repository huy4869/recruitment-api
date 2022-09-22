<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\MasterDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataController extends BaseController
{
    /**
     * MasterDataController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware());
    }

    /**
     * Master data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        $resources = $request->get('resources');

        if (!is_array($resources)) {
            return $this->sendSuccessResponse([]);
        }

        $data = MasterDataService::getInstance()->withResources($resources)->get();
        return $this->sendSuccessResponse($data);
    }
}
