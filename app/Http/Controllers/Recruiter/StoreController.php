<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\StoreCollection;
use App\Services\Recruiter\Store\StoreTableService;
use App\Services\Recruiter\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    protected $storeService;
    protected $storeTableService;
    public function __construct(StoreService $storeService, StoreTableService $storeTableService)
    {
        $this->storeService = $storeService;
        $this->storeTableService = $storeTableService;
    }

    /**
     * List store
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $data = $this->storeTableService->withUser($this->guard()->user())->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new StoreCollection($data));
    }

    /**
     * delete store
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\InputException
     */
    public function delete($id)
    {
        $data = $this->storeService->withUser($this->guard()->user())->delete($id);

        return $this->sendSuccessResponse($data, trans('response.INF.005'));
    }

    /**
     * @return JsonResponse
     */
    public function listStoreNameByOwner()
    {
        $recruiter = $this->guard()->user();
        $data = StoreService::getInstance()->withUser($recruiter)->getAllStoreNameByOwner();

        return $this->sendSuccessResponse($data);
    }
}
