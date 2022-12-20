<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkHistoryRequest;
use App\Http\Requests\Admin\WorkHistoryUpdateRequest;
use App\Http\Resources\Admin\User\DetailWorkHistoryResource;
use App\Services\Admin\WorkHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkHistoryController extends Controller
{
    /**
     * create work history
     *
     * @param WorkHistoryRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(WorkHistoryRequest $request)
    {
        $input = $request->only([
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

        $data = WorkHistoryService::getInstance()
            ->withUser($this->guard()->user())
            ->store($input, $request->get('user_id'));

        return $this->sendSuccessResponse($data, trans('validation.INF.006'));
    }

    /**
     * update work history
     *
     * @param WorkHistoryUpdateRequest $request
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function update(WorkHistoryUpdateRequest $request, $id)
    {
        $input = $request->only([
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

        $data = WorkHistoryService::getInstance()
            ->withUser($this->guard()->user())
            ->update($input, $id, $request->get('user_id'));

        return $this->sendSuccessResponse($data, trans('validation.INF.001'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function delete($id, Request $request)
    {
        WorkHistoryService::getInstance()->delete($id, $request->get('user_id'));

        return $this->sendSuccessResponse([], trans('validation.INF.005'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id, Request $request)
    {
        $data = WorkHistoryService::getInstance()->detail($id, $request->get('user_id'));

        return $this->sendSuccessResponse(new DetailWorkHistoryResource($data));
    }
}
