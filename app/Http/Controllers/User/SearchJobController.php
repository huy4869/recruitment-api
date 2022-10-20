<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\SearchJob\SearchJobCollection;
use App\Services\User\SearchJob\SearchJobService;
use App\Services\User\SearchJob\SearchJobTableService;
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