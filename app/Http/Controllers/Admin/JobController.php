<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Job\JobCollection;
use App\Services\Admin\Job\JobTableService;
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
        $admin = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $jobs = JobTableService::getInstance()->withUser($admin)->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new JobCollection($jobs));
    }
}
