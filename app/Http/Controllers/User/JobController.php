<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Services\User\Job\JobService;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    private $jobService;

    public function __construct(JobService $jobService)
    {
        $this->jobService = $jobService;
    }

    /**
     * delete favorite fob
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function deleteFavoriteJob($id)
    {
        $data = $this->jobService->withUser($this->guard()->user())->deleteFavorite($id);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('response.INF.003'));
        }

        throw new InputException(trans('validation.ERR.011'));
    }

    /**
     * get Favorite Job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavoriteJob()
    {
        $data = $this->jobService->withUser($this->guard()->user())->getFavoriteJobs();

        return $this->sendSuccessResponse($data);
    }
}
