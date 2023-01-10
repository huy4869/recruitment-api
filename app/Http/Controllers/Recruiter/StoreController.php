<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Recruiter\CreateStoreRequest;
use App\Http\Requests\Recruiter\UpdateStoreRequest;
use App\Http\Resources\Recruiter\StoreCollection;
use App\Http\Resources\Recruiter\StoreDetailResource;
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

        return $this->sendSuccessResponse($data, trans('validation.INF.005'));
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

    public function detail($id)
    {
        $data = $this->storeService->withUser($this->guard()->user())->detail($id);

        if (!$data) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
        }

        return ResponseHelper::sendResponse(
            is_null(current($data)->deleted_at) ? ResponseHelper::STATUS_CODE_SUCCESS : ResponseHelper::STATUS_CODE_BAD_REQUEST,
            '',
            StoreDetailResource::collection($data)
        );
    }

    /**
     * create
     *
     * @param CreateStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(CreateStoreRequest $request)
    {
        $rec = $this->guard()->user();
        $input = $request->only([
            'url',
            'store_name',
            'website',
            'tel',
            'application_tel',
            'postal_code',
            'province_id',
            'province_city_id',
            'address',
            'building',
            'manager_name',
            'employee_quantity',
            'founded_year',
            'business_segment',
            'specialize_ids',
            'store_features',
            'recruiter_name',
        ]);

        $data = $this->storeService->withUser($rec)->store($input);

        return $this->sendSuccessResponse($data, trans('validation.INF.010'));
    }

    public function update(UpdateStoreRequest $request, $id)
    {
        $rec = $this->guard()->user();
        $input = $request->only([
            'url',
            'store_name',
            'website',
            'tel',
            'application_tel',
            'postal_code',
            'province_id',
            'province_city_id',
            'address',
            'building',
            'manager_name',
            'employee_quantity',
            'founded_year',
            'business_segment',
            'specialize_ids',
            'store_features',
            'recruiter_name',
        ]);

        $data = $this->storeService->withUser($rec)->update($input, $id);

        return $this->sendSuccessResponse($data, trans('validation.INF.001'));
    }
}
