<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Services\User\ApplicationService;
use App\Services\User\JobPostingService;
use Illuminate\Http\JsonResponse;

class JobPostingController extends BaseController
{
    /**
     * @var ApplicationService
     */
    private $jobPostingService;

    /**
     * ApplicationController constructor.
     * @param JobPostingService $jobPostingService
     */
    public function __construct(JobPostingService $jobPostingService)
    {
        $this->jobPostingService = $jobPostingService;
    }

    /**
     * Check date
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detailJobUserApplication($id)
    {
        $user = $this->guard()->user();
        $data = $this->jobPostingService->withUser($user)->detailJobUserApplication($id);

        return $this->sendSuccessResponse($data);
    }
}
