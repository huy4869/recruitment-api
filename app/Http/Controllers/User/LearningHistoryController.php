<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\LearningHistory\LearningHistoryRequest;
use App\Http\Resources\User\LearningHistory\LearningHistoryResource;
use App\Http\Resources\User\LearningHistory\ListLearningHistoryResource;
use App\Services\User\LearningHistoryService;
use Illuminate\Http\JsonResponse;

class LearningHistoryController extends BaseController
{
    /**
     * @var LearningHistoryService
     */
    private $learningHistoryService;

    /**
     * LearningHistoryController constructor.
     * @param LearningHistoryService $learningHistoryService
     */
    public function __construct(LearningHistoryService $learningHistoryService)
    {
        $this->learningHistoryService = $learningHistoryService;
    }

    /**
     * List user learning histories
     *
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $data = $this->learningHistoryService->withUser($user)->list();

        return $this->sendSuccessResponse(ListLearningHistoryResource::collection($data));
    }

    /**
     * User store learning history
     *
     * @param LearningHistoryRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(LearningHistoryRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $this->makeDataInputs($request);
        $data = $this->learningHistoryService->withUser($user)->store($inputs);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.006'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }

    /**
     * Detail user learning history
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $user = $this->guard()->user();
        $data = $this->learningHistoryService->withUser($user)->detail($id);

        return $this->sendSuccessResponse(new LearningHistoryResource($data));
    }

    /**
     * Update user learning history
     *
     * @param $id
     * @param LearningHistoryRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function update($id, LearningHistoryRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $this->makeDataInputs($request);
        $data = $this->learningHistoryService->withUser($user)->update($id, $inputs);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.001'));
        }

        throw new InputException(trans('validation.ERR.007'));
    }

    /**
     * Delete user learning history
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function delete($id)
    {
        $user = $this->guard()->user();
        $data = $this->learningHistoryService->withUser($user)->delete($id);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.005'));
        }

        throw new InputException(trans('validation.ERR.008'));
    }

    /**
     * @param $request
     * @return mixed
     */
    private function makeDataInputs($request)
    {
        return $request->only([
            'learning_status_id',
            'school_name',
            'enrollment_period_start',
            'enrollment_period_end',
        ]);
    }
}
