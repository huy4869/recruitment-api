<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\FavoriteJobResource;
use App\Http\Resources\User\Job\DetailJobPostingResource;
use App\Http\Resources\User\Job\JobCollection;
use App\Http\Resources\User\Job\JobPostingResource;
use App\Models\JobPosting;
use App\Services\User\Job\JobTableService;
use App\Services\User\Job\JobService;
use App\Services\User\SearchJob\SearchJobService;
use App\Services\User\UserJobDesiredMatchService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    private $jobService;

    public function __construct(JobService $jobService)
    {
        $this->jobService = $jobService;
    }

    public function detail($id)
    {
        $user = $this->guard()->user();
        $job = JobService::getInstance()->withUser($user)->detail($id);

        if (!$job) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, trans('response.not_found'));
        }

        return ResponseHelper::sendResponse(
            $job['job_status_id'] == JobPosting::STATUS_RELEASE && is_null($job['deleted_at']) ? ResponseHelper::STATUS_CODE_SUCCESS : ResponseHelper::STATUS_CODE_BAD_REQUEST,
            '',
            new DetailJobPostingResource($job)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function recentJobs(Request $request)
    {
        $user = $this->guard()->user();
        $jobs = JobService::getInstance()->withUser($user)->getRecentJobs($request->get('ids'));

        return $this->sendSuccessResponse(JobPostingResource::collection($jobs));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function suggestJobs($id)
    {
        $jobs = JobService::getInstance()->withUser($this->guard()->user())->getSuggestJobs($id);

        return $this->sendSuccessResponse(JobPostingResource::collection($jobs));
    }

    /**
     * Get list new jobs
     *
     * @return JsonResponse
     */
    public function getListNewJobPostings()
    {
        $user = $this->guard()->user();
        $data = JobService::getInstance()->withUser($user)->getListNewJobPostings();

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
        $jobPostings = JobService::getInstance()->withUser($this->guard()->user())->getListMostViewJobPostings();

        return $this->sendSuccessResponse(JobPostingResource::collection($jobPostings));
    }

    /**
     * Get most favorite jobs
     *
     * @return JsonResponse
     */
    public function getListMostFavoriteJobPostings()
    {
        $jobPostings = JobService::getInstance()->getListMostFavoriteJobPostings();

        return $this->sendSuccessResponse(JobPostingResource::collection($jobPostings));
    }

    /**
     * Get recommend jobs
     *
     * @return JsonResponse
     */
    public function getListRecommends()
    {
        $user = $this->guard()->user();

        if ($user) {
            $jobPostings = UserJobDesiredMatchService::getInstance()->withUser($user)->getListMatch();
        } else {
            $jobPostings = JobService::getInstance()->getListMostFavoriteJob();
        }

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
            return $this->sendSuccessResponse($data, trans('validation.INF.003'));
        }

        throw new InputException(trans('validation.ERR.011'));
    }

    /**
     * get Favorite Job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavoriteJob(Request $request)
    {
        $data = $this->jobService->withUser($this->guard()->user())->getFavoriteJobs($request->get('per_page'));

        return $this->sendSuccessResponse([
            'data' => FavoriteJobResource::collection($data['favoriteJob']),
            'per_page' => $data['per_page'],
            'current_page' => $data['current_page'],
            'total_page' => $data['total_page'],
            'total' => $data['total'],
        ]);
    }

    /**
     * create store
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function storeFavorite(Request $request)
    {
        $user = $this->guard()->user();

        $data = $this->jobService->withUser($user)->storeFavorite($request->get('job_posting_id'));

        return $this->sendSuccessResponse($data, trans('validation.INF.020'));
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function list(Request $request)
    {
        $user = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);

        try {
            DB::beginTransaction();

            $jobs = JobTableService::getInstance()->withUser($user)->data($search, $orders, $filters, $perPage);

            if ($user) {
                $allowKeyFilter = [
                    'work_type_ids',
                    'job_type_ids',
                    'experience_ids',
                    'feature_ids',
                    'order_by_id',
                    'province_id',
                    'province_city_id'
                ];

                $allowStoreSearchCond = $search || $filters;

                if ($filters) {
                    foreach ($filters as $filter) {
                        if (
                            !isset($filter['key'])
                            || !isset($filter['data'])
                            || !in_array($filter['key'], $allowKeyFilter)
                        ) {
                            $allowStoreSearchCond = false;
                            break;
                        }
                    }
                }

                if ($allowStoreSearchCond) {
                    SearchJobService::getInstance()->withUser($user)->store($search, $filters);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try

        return $this->sendSuccessResponse(new JobCollection($jobs));
    }

    /**
     * @return JsonResponse
     */
    public function totalJobs()
    {
        $total = JobService::getInstance()->withUser($this->guard()->user())->getTotalJobs();

        return $this->sendSuccessResponse($total);
    }

    /**
     * Check date
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detailJobUserApplication($id)
    {
        $user = $this->guard()->user();
        $data = $this->jobService->withUser($user)->detailJobUserApplication($id);

        return $this->sendSuccessResponse($data);
    }
}
