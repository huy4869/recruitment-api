<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Resources\Admin\User\DetailUserResource;
use App\Http\Resources\Admin\User\UserCollection;
use App\Services\Admin\User\UserService;
use App\Services\Admin\User\UserTableService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware());
    }

    /**
     * List user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $data = UserTableService::getInstance()->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new UserCollection($data));
    }

    /**
     * Show user detail
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id): JsonResponse
    {
        $data = UserService::getInstance()->detail($id);

        return $this->sendSuccessResponse(new DetailUserResource($data));
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(StoreRequest $request)
    {
        $inputs = $request->only([
            'role_id',
            'first_name',
            'last_name',
            'furi_first_name',
            'furi_last_name',
            'email',
            'password',
            'store_ids',
        ]);
        $data = UserService::getInstance()->store($inputs);

        return $this->sendSuccessResponse($data, trans('validation.INF.010'));
    }

    /**
     * @param $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update($id, UpdateRequest $request)
    {
        $admin = $this->guard()->user();
        $inputs = $request->only([
            'first_name',
            'last_name',
            'furi_first_name',
            'furi_last_name',
            'password',
            'store_ids',
        ]);
        $data = UserService::getInstance()->withUser($admin)->update($id, $inputs);

        return $this->sendSuccessResponse($data, trans('validation.INF.001'));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function destroy($id)
    {
        $admin = $this->guard()->user();
        $result = UserService::getInstance()->withUser($admin)->destroy($id);

        return $this->sendSuccessResponse($result, trans('validation.INF.005'));
    }
}
