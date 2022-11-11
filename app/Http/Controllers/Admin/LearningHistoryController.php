<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LearningHistoryRequest;
use App\Services\Admin\LearningHistoryService;
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
     * @return \Illuminate\Http\JsonResponse
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
            return $this->sendSuccessResponse($data, trans('response.INF.006'));
        }

        throw new InputException(trans('response.ERR.006'));
    }

    /**
     * update learning history
     *
     * @param LearningHistoryRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
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
            return $this->sendSuccessResponse($data, trans('response.INF.001'));
        }

        throw new InputException(trans('response.ERR.007'));
    }
}
