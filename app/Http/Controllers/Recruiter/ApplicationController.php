<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recruiter\Application\UpdateRequest;
use App\Http\Resources\Recruiter\Application\ApplicationCollection;
use App\Http\Resources\Recruiter\Application\ApplicationProfileUserResource;
use App\Http\Resources\Recruiter\Application\DetailApplicationResource;
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

        if ($data) {
            return $this->sendSuccessResponse(new ApplicationProfileUserResource($data));
        }

        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, []);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $recruiter = $this->guard()->user();
        $application = ApplicationService::getInstance()->withUser($recruiter)->getDetail($id);

        if (!$application) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, []);
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
        $recruiter = $this->guard()->user();
        $inputs = $request->only([
            'interview_status_id',
            'owner_memo',
            'meet_link',
        ]);
        $result = ApplicationService::getInstance()->withUser($recruiter)->update($id, $inputs);

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
}
