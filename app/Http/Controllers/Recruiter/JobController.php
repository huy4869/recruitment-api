<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recruiter\Job\CreateRequest;
use App\Http\Requests\Recruiter\Job\UpdateRequest;
use App\Http\Resources\Recruiter\Job\DetailJobResource;
use App\Http\Resources\Recruiter\Job\JobCollection;
use App\Models\JobPosting;
use App\Services\Recruiter\Job\JobTableService;
use App\Services\Recruiter\Job\JobService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * @param CreateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(CreateRequest $request)
    {
        $recruiter = $this->guard()->user();
        $inputs = self::makeRequestData($request);
        $result = JobService::getInstance()->withUser($recruiter)->create($inputs);

        if ($result) {
            switch ($request->job_status_id) {
                case JobPosting::STATUS_DRAFT:
                    $msg = trans('validation.INF.009');
                    break;
                case JobPosting::STATUS_RELEASE:
                    $msg = trans('validation.INF.010');
                    break;
                case JobPosting::STATUS_END:
                    $msg = trans('validation.INF.012');
                    break;
                default:
                    $msg = trans('response.INF.006');
                    break;
            }

            return $this->sendSuccessResponse($result, $msg);
        }

        return $this->sendSuccessResponse($result, trans('validation.INF.009'));
    }

    /**
     * @param $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update($id, UpdateRequest $request)
    {
        $recruiter = $this->guard()->user();
        $inputs = self::makeRequestData($request);
        $jobStatusIds = JobService::getInstance()->withUser($recruiter)->update($id, $inputs);

        switch ($jobStatusIds) {
            case JobPosting::STATUS_DRAFT:
                $msg = trans('validation.INF.009');
                break;
            case JobPosting::STATUS_RELEASE:
                $msg = trans('validation.INF.010');
                break;
            case JobPosting::STATUS_END:
                $msg = trans('validation.INF.012');
                break;
            default:
                $msg = trans('response.INF.006');
                break;
        }

        return $this->sendSuccessResponse($jobStatusIds, $msg);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $recruiter = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $jobs = JobTableService::getInstance()->withUser($recruiter)->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new JobCollection($jobs));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $recruiter = $this->guard()->user();
        $job = JobService::getInstance()->withUser($recruiter)->getDetail($id);

        return $this->sendSuccessResponse(new DetailJobResource($job));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function destroy($id)
    {
        $recruiter = $this->guard()->user();
        $response = JobService::getInstance()->withUser($recruiter)->destroy($id);

        return $this->sendSuccessResponse($response, trans('validation.INF.005'));
    }

    /**
     * @return JsonResponse
     */
    public function listJobNameByOwner()
    {
        $recruiter = $this->guard()->user();
        $data = JobService::getInstance()->withUser($recruiter)->getAllJobNameByOwner();

        return $this->sendSuccessResponse($data);
    }

    /**
     * @param $request
     * @return mixed
     */
    private function makeRequestData($request)
    {
        return $request->only([
            'name',
            'store_id',
            'job_status_id',
            'pick_up_point',
            'job_banner',
            'job_thumbnails',
            'job_type_ids',
            'description',
            'work_type_ids',
            'salary_type_id',
            'salary_min',
            'salary_max',
            'salary_description',
            'start_work_time',
            'end_work_time',
            'shifts',
            'age_min',
            'age_max',
            'gender_ids',
            'experience_ids',
            'postal_code',
            'province_id',
            'province_city_id',
            'working_days',
            'range_hours_type',
            'address',
            'building',
            'station_ids',
            'welfare_treatment_description',
            'feature_ids',
        ]);
    }
}
