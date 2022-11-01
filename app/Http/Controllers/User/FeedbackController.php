<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\FeedbackRequest;
use App\Models\JobPosting;
use App\Services\User\FeedbackService;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * Create feedback
     *
     * @param JobPosting $jobPosting
     * @param FeedbackRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(JobPosting $jobPosting, FeedbackRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only(['feedback_type_ids', 'content']);
        $data = FeedbackService::getInstance()->withUser($user)->store($jobPosting, $inputs);

        return $this->sendSuccessResponse($data, trans('validation.INF.008'));
    }
}
