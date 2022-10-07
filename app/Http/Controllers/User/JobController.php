<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Services\User\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
