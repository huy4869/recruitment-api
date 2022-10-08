<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\WorkHistory\WorkHistoryRequest;
use App\Http\Resources\User\WorkHistory\ListResource;
use App\Services\User\WorkHistoryService;
use Illuminate\Http\JsonResponse;

class WorkHistoryController extends BaseController
{
    /**
     * List user work histories
     *
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $data = WorkHistoryService::getInstance()->withUser($user)->list();

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
        $data = WorkHistoryService::getInstance()->withUser($user)->store($inputs);

        return $this->sendSuccessResponse($data, trans('response.created', [
            'object' => trans('response.label.user_work_history')
        ]));
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
