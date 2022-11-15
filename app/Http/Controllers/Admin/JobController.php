<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Job\DetailJobResource;
use App\Http\Resources\Admin\Job\JobCollection;
use App\Models\JobPosting;
use App\Services\Admin\Job\JobTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Job\CreateRequest;
use App\Services\Admin\Job\JobService;
use Exception;

class JobController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $admin = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $jobs = JobTableService::getInstance()->withUser($admin)->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new JobCollection($jobs));
    }

    /**
     * Create job
     *
     * @param CreateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(CreateRequest $request)
    {
        $admin = $this->guard()->user();
        $inputs = self::makeRequestData($request);
        $data = JobService::getInstance()->withUser($admin)->create($inputs);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.010'));
        }

        return $this->sendSuccessResponse($data, trans('validation.INF.009'));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $job = JobService::getInstance()->getDetail($id);

        return $this->sendSuccessResponse(new DetailJobResource($job));
    }

    /**
     * @param $id
     * @param CreateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update($id, CreateRequest $request)
    {
        $inputs = self::makeRequestData($request);
        $job = JobService::getInstance()->update($id, $inputs);

        switch ($job->job_status_id) {
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

        return $this->sendSuccessResponse([], $msg);
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
            'working_days',
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
            'building',
            'address',
            'station_ids',
            'welfare_treatment_description',
            'feature_ids',
        ]);
    }
}
