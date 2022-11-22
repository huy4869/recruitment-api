<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Application\UpdateRequest;
use App\Http\Resources\Admin\Application\ApplicationProfileUserResource;
use App\Http\Resources\Admin\Application\DetailApplicationResource;
use App\Services\Admin\Application\ApplicationService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Admin\Application\ApplicationCollection;
use App\Services\Admin\Application\ApplicationTableService;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $admin = $this->guard()->user();
        $application = ApplicationService::getInstance()->withUser($admin)->getDetail($id);

        return $this->sendSuccessResponse(new DetailApplicationResource($application));
    }

    /**
     * @param $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function update($id, UpdateRequest $request)
    {
        $admin = $this->guard()->user();
        $inputs = $request->only([
            'interview_status_id',
            'owner_memo',
        ]);
        $result = ApplicationService::getInstance()->withUser($admin)->update($id, $inputs);

        return $this->sendSuccessResponse($result, trans('validation.INF.013'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $application = ApplicationTableService::getInstance()->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new ApplicationCollection($application));
    }

    public function profileUser($id)
    {
        $data = ApplicationService::getInstance()->profileUser($id);

        return $this->sendSuccessResponse($data);
    }
}
