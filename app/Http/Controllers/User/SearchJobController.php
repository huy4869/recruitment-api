<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SearchJob\StoreRequest;
use App\Http\Resources\User\SearchJob\SearchJobCollection;
use App\Services\User\SearchJob\SearchJobService;
use App\Services\User\SearchJob\SearchJobTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchJobController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $user = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $jobSearch = SearchJobTableService::getInstance()->withUser($user)
            ->data($search, $orders, $filters, $perPage);

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
