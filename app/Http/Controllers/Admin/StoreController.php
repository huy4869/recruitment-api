<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Store\StoreRequest;
use App\Http\Requests\Admin\Store\UpdateRequest;
use App\Http\Resources\Admin\StoreCollection;
use App\Http\Resources\Admin\StoreDetailResource;
use App\Services\Admin\Store\StoreService;
use App\Services\Admin\Store\StoreTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    private $storeTableService;
    private $storeService;

    public function __construct(StoreService $storeService, StoreTableService $storeTableService)
    {
        $this->storeTableService = $storeTableService;
        $this->storeService = $storeService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $data = $this->storeTableService->data(
            null,
            null,
            $request->get('filters'),
            $request->get('per_page')
        );

        return $this->sendSuccessResponse(new StoreCollection($data));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
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
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(StoreRequest $request)
    {
        $input = $request->only([
            'user_id',
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

        $data = $this->storeService->withUser($this->guard()->user())->store($input);

        return $this->sendSuccessResponse($data, trans('validation.INF.010'));
    }

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function update(UpdateRequest $request, $id)
    {
        $input = $request->only([
            'user_id',
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

        $data = $this->storeService->withUser($this->guard()->user())->update($input, $id);

        return $this->sendSuccessResponse($data, trans('validation.INF.001'));
    }

    public function delete($id)
    {
        $data = $this->storeService->withUser($this->guard()->user())->delete($id);

        return $this->sendSuccessResponse($data, trans('validation.INF.005'));
    }

    /**
     * @return JsonResponse
     */
    public function all()
    {
        $data = StoreService::getInstance()->all();

        return $this->sendSuccessResponse($data);
    }
}
