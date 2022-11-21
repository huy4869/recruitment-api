<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\WorkHistory\WorkHistoryRequest;
use App\Http\Resources\User\WorkHistory\DetailResource;
use App\Http\Resources\User\WorkHistory\ListResource;
use App\Services\User\WorkHistoryService;
use Illuminate\Http\JsonResponse;

class WorkHistoryController extends BaseController
{
    private $workHistoryService;

    /**
     * WorkHistoryController constructor.
     * @param WorkHistoryService $workHistoryService
     */
    public function __construct(WorkHistoryService $workHistoryService)
    {
        $this->workHistoryService = $workHistoryService;
    }

    /**
     * List user work histories
     *
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $data = $this->workHistoryService->withUser($user)->list();

        return $this->sendSuccessResponse(ListResource::collection($data));
    }

    /**
     * User store work history
     *
     * @param WorkHistoryRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(WorkHistoryRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $this->makeDataInputs($request);
        $data = $this->workHistoryService->withUser($user)->store($inputs);

        return $this->sendSuccessResponse($data, trans('validation.INF.006'));
    }

    /**
     * Detail user work history
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $user = $this->guard()->user();
        $data = $this->workHistoryService->withUser($user)->detail($id);

        return $this->sendSuccessResponse(new DetailResource($data));
    }

    /**
     * Update user work history
     *
     * @param $id
     * @param WorkHistoryRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function update($id, WorkHistoryRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $this->makeDataInputs($request);
        $data = $this->workHistoryService->withUser($user)->update($id, $inputs);

        return $this->sendSuccessResponse($data, trans('validation.INF.001'));
    }

    /**
     * Delete user work history
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function delete($id)
    {

        $user = $this->guard()->user();
        $this->workHistoryService->withUser($user)->delete($id);

        return $this->sendSuccessResponse([], trans('validation.INF.005'));
    }

    /**
     * @param $request
     * @return mixed
     */
    private function makeDataInputs($request)
    {
        return $request->only([
            'job_types',
            'work_types',
            'store_name',
            'company_name',
            'period_start',
            'period_end',
            'position_offices',
            'business_content',
            'experience_accumulation',
        ]);
    }
}
