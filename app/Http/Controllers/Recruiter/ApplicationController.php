<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\Application\ApplicationCollection;
use App\Http\Resources\Recruiter\Application\ApplicationProfileUserResource;
use App\Services\Recruiter\Application\ApplicationService;
use App\Services\Recruiter\Application\ApplicationTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $recruiter = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $application = ApplicationTableService::getInstance()->withUser($recruiter)->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new ApplicationCollection($application));
    }

    public function profileUser($id)
    {
        $data = ApplicationService::getInstance()->profileUser($id);

        return $this->sendSuccessResponse(new ApplicationProfileUserResource($data));
    }
}
