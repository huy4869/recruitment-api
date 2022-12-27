<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InterviewScheduleDateRequest;
use App\Http\Requests\Admin\ListInterviewScheduleRequest;
use App\Http\Requests\Admin\UpdateApplicationRequest;
use App\Http\Requests\Admin\UpdateOrCreateInterviewScheduleRequest;
use App\Services\Admin\InterviewScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InterviewScheduleController extends Controller
{
    /**
     * @var InterviewScheduleService
     */
    private $interviewScheduleService;

    /**
     * InterviewScheduleController constructor.
     * @param InterviewScheduleService $interviewScheduleService
     */
    public function __construct(InterviewScheduleService $interviewScheduleService)
    {
        $this->interviewScheduleService = $interviewScheduleService;
    }

    /**
     * Get interview schedule
     *
     * @param ListInterviewScheduleRequest $request
     * @return JsonResponse
     */
    public function getInterviewSchedule(ListInterviewScheduleRequest $request)
    {
        $inputs = $request->only(['start_date', 'store_id']);
        $data = $this->interviewScheduleService->getInterviewSchedule($inputs);

        return $this->sendSuccessResponse($data);
    }

    /**
     * Admin update application
     *
     * @param $applicationId
     * @param UpdateApplicationRequest $request
     * @return JsonResponse
     * @throws InputException
     * @throws ValidationException
     */
    public function updateApplication($applicationId, UpdateApplicationRequest $request)
    {
        $inputs = $request->only(['date', 'hours', 'interview_approaches_id', 'note']);
        $this->interviewScheduleService->updateApplication($applicationId, $inputs);

        return $this->sendSuccessResponse([], trans('validation.INF.008'));
    }

    /**
     * Admin update or create interview schedule
     *
     * @param UpdateOrCreateInterviewScheduleRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateInterviewSchedule(UpdateOrCreateInterviewScheduleRequest $request)
    {
        $this->interviewScheduleService->updateOrCreateInterviewSchedule($request->all());

        return $this->sendSuccessResponse([], trans('validation.INF.016'));
    }

    /**
     * Admin update or create interview schedule date
     *
     * @param InterviewScheduleDateRequest $request
     * @return JsonResponse
     */
    public function updateOrCreateInterviewScheduleDate(InterviewScheduleDateRequest $request)
    {
        $date = $request->get('date');
        $storeId = $request->get('store_id');
        $data = $this->interviewScheduleService->updateOrCreateInterviewScheduleDate($date, $storeId);

        return $this->sendSuccessResponse($data, trans('validation.INF.016'));
    }

    /**
     * Detail application
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function getInterviewScheduleApplication($id)
    {
        $data = $this->interviewScheduleService->detailUserApplication($id);

        return $this->sendSuccessResponse($data);
    }
}
