<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Job\UpdateRequest;
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
    public function create(CreateRequest $request)
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

        if (!$job) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
        }

        return ResponseHelper::sendResponse(
            is_null($job->deleted_at) ? ResponseHelper::STATUS_CODE_SUCCESS : ResponseHelper::STATUS_CODE_BAD_REQUEST,
            '',
            new DetailJobResource($job));
    }

    /**
     * @param $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function update($id, UpdateRequest $request)
    {
        $admin = $this->guard()->user();
        $inputs = self::makeRequestData($request);
        $job = JobService::getInstance()->withUser($admin)->update($id, $inputs);

        switch ($job->job_status_id) {
            case JobPosting::STATUS_DRAFT:
                $msg = trans('validation.INF.009');
                break;
            case JobPosting::STATUS_RELEASE:
                $msg = trans('validation.INF.010');
                break;
            case JobPosting::STATUS_HIDE:
                $msg = trans('validation.INF.024');
                break;
            case JobPosting::STATUS_END:
                $msg = trans('validation.INF.012');
                break;
            default:
                $msg = trans('validation.INF.006');
                break;
        }

        return $this->sendSuccessResponse([], $msg);
    }

    /**
     * delete job posting
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function delete($id)
    {
        $admin = $this->guard()->user();
        $response = JobService::getInstance()->withUser($admin)->delete($id);

        return $this->sendSuccessResponse($response, trans('validation.INF.005'));
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
            'range_hours_type',
            'start_work_time_type',
            'end_work_time_type',
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
