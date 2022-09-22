<?php

namespace App\Http\Controllers\User;

use App\Services\User\MasterDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataController extends BaseController
{
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
