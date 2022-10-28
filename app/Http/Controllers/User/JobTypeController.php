<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\JobTypeService;
use Illuminate\Http\JsonResponse;

class JobTypeController extends Controller
{
    private $jobTypeService;

    public function __construct(JobTypeService $jobTypeService)
    {
        $this->jobTypeService = $jobTypeService;
    }

    /**
     * amount job in work type
     *
     * @return JsonResponse
     */
    public function amountJobInJobTypes()
    {
        $data = $this->jobTypeService->amountJobInJobTypes();

        return $this->sendSuccessResponse($data);
    }
}
