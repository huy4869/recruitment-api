<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\JobPosting\JobPostingCollection;
use App\Http\Resources\User\JobPosting\JobPostingResource;
use App\Services\User\Job\JobService;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    private $jobService;

    public function __construct(JobService $jobService)
    {
        $this->jobService = $jobService;
    }

    /**
     * Get list new jobs
     *
     * @return JsonResponse
     */
    public function getListNewJobPostings()
    {
        $user = $this->guard()->user();
        $data = JobService::getInstance()->getListNewJobPostings();

        return $this->sendSuccessResponse([
            'total_jobs' => $data['total_jobs'],
            'data' => JobPostingResource::collection($data['list_jobs']),
        ]);
    }

    /**
     * Get most view jobs
     *
     * @return JsonResponse
     */
    public function getListMostViewJobPostings()
    {
        $jobPostings = JobService::getInstance()->getListMostViewJobPostings();

        return $this->sendSuccessResponse(JobPostingResource::collection($jobPostings));
    }

    /**
     * Get most apply jobs
     *
     * @return JsonResponse
     */
    public function getListMostApplyJobPostings()
    {
        $jobPostings = JobService::getInstance()->getListMostApplyJobPostings();

        return $this->sendSuccessResponse(JobPostingResource::collection($jobPostings));
    }

    /**
     * delete favorite fob
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function deleteFavoriteJob($id)
    {
        $data = $this->jobService->withUser($this->guard()->user())->deleteFavorite($id);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('response.INF.003'));
        }

        throw new InputException(trans('validation.ERR.011'));
    }

    /**
     * get Favorite Job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavoriteJob()
    {
        $data = $this->jobService->withUser($this->guard()->user())->getFavoriteJobs();

        return $this->sendSuccessResponse($data);
    }
}
