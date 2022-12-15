<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LearningHistoryRequest;
use App\Http\Resources\Admin\User\DetailLearningHistoryResource;
use App\Services\Admin\LearningHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningHistoryController extends Controller
{
    private $learningHistory;

    public function __construct(LearningHistoryService $learningHistory)
    {
        $this->learningHistory = $learningHistory;
    }

    /**
     * create learning history
     *
     * @param LearningHistoryRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(LearningHistoryRequest $request)
    {
        $input = $request->only([
            'learning_status_id',
            'school_name',
            'enrollment_period_start',
            'enrollment_period_end',
        ]);

        $data = $this->learningHistory->store($input, $request->get('user_id'));

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.006'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }

    /**
     * update learning history
     *
     * @param LearningHistoryRequest $request
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function update(LearningHistoryRequest $request, $id)
    {
        $input = $request->only([
            'learning_status_id',
            'school_name',
            'enrollment_period_start',
            'enrollment_period_end',
        ]);

        $data = $this->learningHistory->update($input, $id, $request->get('user_id'));

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.001'));
        }

        throw new InputException(trans('validation.ERR.007'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id, Request $request)
    {
        $data = $this->learningHistory->detail($id, $request->get('user_id'));

        return $this->sendSuccessResponse(new DetailLearningHistoryResource($data));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function delete($id, Request $request)
    {
        $this->learningHistory->delete($id, $request->get('user_id'));

        return $this->sendSuccessResponse([], trans('validation.INF.005'));
    }
}
