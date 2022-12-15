<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\DesiredConditionRequest;
use App\Http\Resources\User\DesiredCondition\DesiredConditionResource;
use App\Services\User\DesiredConditionService;
use Illuminate\Http\JsonResponse;

class DesiredConditionController extends BaseController
{
    /**
     * @var DesiredConditionService
     */
    private $desiredConditionService;

    /**
     * DesiredConditionController constructor.
     * @param DesiredConditionService $desiredConditionService
     */
    public function __construct(DesiredConditionService $desiredConditionService)
    {
        $this->desiredConditionService = $desiredConditionService;
    }

    /**
     * Detail user desired condition
     *
     * @return JsonResponse
     * @throws InputException
     */
    public function detail()
    {
        $user = $this->guard()->user();
        $data = $this->desiredConditionService->withUser($user)->detail();

        return $this->sendSuccessResponse(new DesiredConditionResource($data));
    }

    /**
     * Store or update user desired condition
     *
     * @param DesiredConditionRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function storeOrUpdate(DesiredConditionRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $this->makeDataInputs($request);
        $data = $this->desiredConditionService->withUser($user)->storeOrUpdate($inputs);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.001'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }

    /**
     * @param $request
     * @return mixed
     */
    private function makeDataInputs($request)
    {
        return $request->only([
            'province_ids',
            'work_type_ids',
            'age_id',
            'salary_type_id',
            'salary_min',
            'salary_max',
            'job_type_ids',
            'job_experience_ids',
            'job_feature_ids',
            'working_days',
            'start_working_time',
            'end_working_time',
        ]);
    }
}
