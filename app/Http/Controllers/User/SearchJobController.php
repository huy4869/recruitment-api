<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SearchJob\StoreRequest;
use App\Http\Resources\User\SearchJob\SearchJobCollection;
use App\Services\User\SearchJob\SearchJobService;
use App\Services\User\SearchJob\SearchJobTableService;
use http\Env\Request;
use Illuminate\Http\JsonResponse;

class SearchJobController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $jobSearch = SearchJobTableService::getInstance()->withUser($user)
            ->data(null, null, null, config('paginate.search_job.per_page'));

        return $this->sendSuccessResponse(new SearchJobCollection($jobSearch));
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only([
            'user_id',
            'work_type_ids',
            'job_type_ids',
            'feature_ids',
            'experience_ids',
            'province_id',
            'province_city_id',
            'text',
        ]);
        $result = SearchJobService::getInstance()->withUser($user)->store($inputs);

        return $this->sendSuccessResponse($result);
    }

    /**
     * @return JsonResponse
     * @throws InputException
     */
    public function destroy($id)
    {
        $user = $this->guard()->user();
        $response = SearchJobService::getInstance()->withUser($user)->destroy($id);

        return $this->sendSuccessResponse($response, trans('validation.INF.005'));
    }
}
