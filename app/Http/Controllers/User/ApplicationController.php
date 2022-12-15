<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\Application\StoreRequest;
use App\Http\Requests\User\Application\UpdateRequest;
use App\Http\Resources\User\Application\ListApplicationResource;
use App\Http\Resources\User\Application\ListInterviewResource;
use App\Services\User\ApplicationService;
use App\Services\User\ApplicationUserHistoryService;
use App\Services\User\Job\JobService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ApplicationController extends BaseController
{
    /**
     * @var ApplicationService
     */
    private $applicationService;

    /**
     * @var JobService
     */
    private $jobPostingService;

    /**
     * @var ApplicationUserHistoryService
     */
    private $applicationUserHistoryService;

    /**
     * ApplicationController constructor.
     * @param ApplicationService $applicationService
     * @param JobService $jobPostingService
     * @param ApplicationUserHistoryService $applicationUserHistoryService
     */
    public function __construct(ApplicationService $applicationService, JobService $jobPostingService, ApplicationUserHistoryService $applicationUserHistoryService)
    {
        $this->applicationService = $applicationService;
        $this->jobPostingService = $jobPostingService;
        $this->applicationUserHistoryService = $applicationUserHistoryService;
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

        return $this->sendSuccessResponse([
            'data' => ListInterviewResource::collection($data['interviews']),
            'view_all' => $data['view_all'],
        ]);
    }

    /**
     * List applied
     *
     * @return JsonResponse
     */
    public function listApplied(Request $request)
    {
        $user = $this->guard()->user();
        $data = $this->applicationService->withUser($user)->getApplied($request->get('all'));

        return $this->sendSuccessResponse([
            'data' => ListInterviewResource::collection($data['interviews']),
            'view_all' => $data['view_all'],
        ]);
    }

    /**
     * Cancel applied
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function cancelApplied($id)
    {
        $user = $this->guard()->user();
        $result = $this->applicationService->withUser($user)->cancelApplied($id);

        return $this->sendSuccessResponse($result, trans('validation.INF.004'));
    }


    /**
     * user application
     *
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws InputException
     * @throws ValidationException
     */
    public function store(StoreRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only(['id', 'date', 'hours', 'interview_approaches_id', 'note']);
        $jobPosting = $this->jobPostingService->withUser($user)->checkSchedulingApplication($inputs);

        try {
            DB::beginTransaction();

            $data = $this->jobPostingService->withUser($user)->store($inputs, $jobPosting);
            $this->applicationUserHistoryService->withUser($user)->storeNotifications($data, $jobPosting);
            $this->applicationUserHistoryService->storeChat($data, $jobPosting);
            $this->applicationUserHistoryService->storeApplicationWorkHistories($data);
            $this->applicationUserHistoryService->storeApplicationLearningHistories($data);
            $this->applicationUserHistoryService->storeApplicationLicensesQualificationHistories($data);
            $this->applicationUserHistoryService->storeApplicationUser($data);

            DB::commit();

            return $this->sendSuccessResponse($data, trans('validation.INF.018'));
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            return $this->sendErrorResponse(trans('validation.EXC.001'));
        }
    }

    /**
     * Detail application
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $user = $this->guard()->user();
        $application = $this->applicationService->withUser($user)->detail($id);
        $times = $this->jobPostingService->withUser($user)->detailJobUserApplication($application->job_posting_id, $application);
        $data = $this->applicationService->withUser($user)->detailApplicationAndTimes($application, $times);

        return $this->sendSuccessResponse($data);
    }

    /**
     * Update application
     *
     * @param $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws InputException|ValidationException
     */
    public function update($id, UpdateRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only(['date', 'hours', 'interview_approaches_id', 'note']);
        $this->applicationService->withUser($user)->updateApplication($id, $inputs);

        return $this->sendSuccessResponse([], trans('validation.INF.019'));
    }
}
