<?php

namespace App\Http\Controllers\User;

use App\Http\Resources\User\WorkHistory\ListResource;
use App\Services\User\WorkHistoryService;
use Illuminate\Http\JsonResponse;

class WorkHistoryController extends BaseController
{
    /**
     * List user work histories
     *
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $data = WorkHistoryService::getInstance()->withUser($user)->list();

        return $this->sendSuccessResponse(ListResource::collection($data));
    }
}
