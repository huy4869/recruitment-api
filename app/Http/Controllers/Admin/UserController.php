<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Http\Resources\Admin\User\UserCollection;
use App\Http\Resources\Admin\User\UserDetailResource;
use App\Http\Resources\Admin\User\UserResource;
use App\Services\Admin\User\UserService;
use App\Services\Admin\User\UserTableService;
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

        return $this->sendSuccessResponse(new UserDetailResource($data));
    }

    /**
     * Update user
     *
     * @param $id
     * @param UserUpdateRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function update($id, UserUpdateRequest $request)
    {
        $inputs = $request->only([
            'name',
            'email',
            'status'
        ]);
        $data = UserService::getInstance()->update($id, $inputs);

        return $this->sendSuccessResponse($data, trans('response.updated', [
            'object' => trans('response.label.user')
        ]));
    }
}
