<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
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

        if (!$application) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
        }

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
            'date',
            'meet_link',
            'hours',
            'interview_approach_id',
            'note',
        ]);
        $result = ApplicationService::getInstance()->withUser($admin)->update($id, $inputs);

        if (!$result) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, '', ['is_user_deleted' => true]);
        }

        if (!is_null($result->jobPostingAcceptTrashed->deleted_at) || !is_null($result->storeAcceptTrashed->deleted_at)) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, '', [
                'is_job_deleted' => !is_null($result->jobPostingAcceptTrashed->deleted_at),
                'is_store_deleted' => !is_null($result->storeAcceptTrashed->deleted_at),
            ]);
        }

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

        if ($data) {
            return $this->sendSuccessResponse($data);
        }

        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
    }
}
