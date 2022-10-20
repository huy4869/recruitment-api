<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\Job\JobCollection;
use App\Services\Recruiter\Job\JobService;
use App\Services\Recruiter\Job\JobTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $recruiter = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $jobs = JobTableService::getInstance()->withUser($recruiter)->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new JobCollection($jobs));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function destroy($id)
    {
        $response = JobService::getInstance()->destroy($id);

        return $this->sendSuccessResponse($response, trans('validation.INF.005'));
    }
}
