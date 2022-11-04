<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recruiter\InterviewScheduleRequest;
use App\Http\Requests\Recruiter\ListInterviewScheduleRequest;
use App\Services\Recruiter\InterviewScheduleService;
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
        $recruiter = $this->guard()->user();
        $date = $request->get('start_date');
        $data = $this->interviewScheduleService->withUser($recruiter)->getInterviewSchedule($date);

        return $this->sendSuccessResponse($data);
    }

    /**
     * Update or create or remove recruiter off time
     *
     * @param InterviewScheduleRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateOrCreateInterviewSchedule(InterviewScheduleRequest $request)
    {
        $recruiter = $this->guard()->user();
        $this->interviewScheduleService->withUser($recruiter)->updateOrCreateInterviewSchedule($request->all());

        return $this->sendSuccessResponse([], trans('validation.INF.016'));
    }
}
