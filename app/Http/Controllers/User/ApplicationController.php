<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\User\Application\CancelAppliedRequest;
use App\Http\Resources\User\Application\ListApplicationResource;
use App\Http\Resources\User\Application\ListInterviewResource;
use App\Services\User\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends BaseController
{
    /**
     * @var ApplicationService
     */
    private $applicationService;

    /**
     * ApplicationController constructor.
     * @param ApplicationService $applicationService
     */
    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * List user applications
     *
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $data = $this->applicationService->withUser($user)->list();

        return $this->sendSuccessResponse(ListApplicationResource::collection($data));
    }

    /**
     * List waiting interview
     *
     * @return JsonResponse
     */
    public function listWaitingInterview(Request $request)
    {
        $user = $this->guard()->user();
        $data = $this->applicationService->withUser($user)->getWaitingInterviews($request->get('all'));

        return $this->sendSuccessResponse(ListInterviewResource::collection($data));
    }

    /**
     * List applied
     *
     * @return JsonResponse
     */
    public function listApplied(Request $request)
    {
        $user = $this->guard()->user();
        $appliedCollection = $this->applicationService->withUser($user)->getApplied($request->get('all'));

        return $this->sendSuccessResponse(ListInterviewResource::collection($appliedCollection));
    }

    /**
     * Cancel applied
     *
     * @param CancelAppliedRequest $request
     * @return JsonResponse
     */
    public function cancelApplied(CancelAppliedRequest $request)
    {
        $result = $this->applicationService->cancelApplied($request->get('application_id'));

        return $this->sendSuccessResponse($result, trans('response.INF_004.cancel_applied'));
    }
}
