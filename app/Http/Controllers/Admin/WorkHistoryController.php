<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkHistoryRequest;
use App\Http\Requests\Admin\WorkHistoryUpdateRequest;
use App\Services\Admin\WorkHistoryService;
use Illuminate\Http\Request;

class WorkHistoryController extends Controller
{
    private $workHistory;

    public function __construct(WorkHistoryService $workHistory)
    {
        $this->workHistory = $workHistory;
    }

    /**
     * create work history
     *
     * @param WorkHistoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\InputException
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

        $data = $this->workHistory->store($input, $request->get('user_id'));

        return $this->sendSuccessResponse($data, trans('validation.INF.006'));
    }

    /**
     * update work history
     *
     * @param WorkHistoryUpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\InputException
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

        $data = $this->workHistory->update($input, $id, $request->get('user_id'));

        return $this->sendSuccessResponse($data, trans('validation.INF.001'));
    }
}
