<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\FeedbackJobs\DetailFeedbackJobResource;
use App\Http\Resources\Admin\FeedbackJobs\FeedbackJobsCollection;
use App\Services\Admin\FeedbackJobs\FeedbackJobsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackJobsController extends Controller
{
    /**
     * List feedback job
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $perPage = $request->per_page;
        $data = FeedbackJobsService::getInstance()->list($perPage);

        return $this->sendSuccessResponse(new FeedbackJobsCollection($data));
    }

    /**
     * Detail feedback job
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $data = FeedbackJobsService::getInstance()->detail($id);

        return $this->sendSuccessResponse(new DetailFeedbackJobResource($data));
    }
}
