<?php

namespace App\Http\Controllers\User;

use App\Http\Resources\User\Application\ListInterviewResource;
use App\Services\User\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends BaseController
{
    /**
     * ApplicationController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware());
    }

    /**
     * List waiting interview
     *
     * @return JsonResponse
     */
    public function listWaitingInterview(Request $request)
    {
        $user = $this->guard()->user();
        $data = ApplicationService::getInstance()->withUser($user)->getWaitingInterviews($request->get('all'));

        return $this->sendSuccessResponse(ListInterviewResource::collection($data));
    }
}
