<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMotivationRequest;
use App\Http\Requests\Admin\UpdatePrRequest;
use App\Http\Requests\Admin\User\UserInfoUpdateRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Http\Resources\Admin\DetailUserInfoResource;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Resources\Admin\User\DetailUserResource;
use App\Http\Resources\Admin\User\UserCollection;
use App\Http\Resources\Admin\User\UserDetailResource;
use App\Http\Resources\Admin\UserInfoCollection;
use App\Services\Admin\User\UserInfoTableService;
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

    /**
     * detail user
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detailUser($id)
    {
        $data = UserService::getInstance()->detailInfoUser($id);

        return $this->sendSuccessResponse(new DetailUserInfoResource($data));
    }

    public function updateUser(UserInfoUpdateRequest $request, $id)
    {
        $input = $request->only([
            'avatar',
            'images',
            'first_name',
            'last_name',
            'alias_name',
            'furi_first_name',
            'furi_last_name',
            'birthday',
            'gender_id',
            'tel',
            'line',
            'facebook',
            'instagram',
            'twitter',
            'postal_code',
            'province_id',
            'province_city_id',
            'address',
            'building',
        ]);

        UserService::getInstance()->updateUser($input, $id);

        return $this->sendSuccessResponse([], trans('validation.INF.001'));
    }

    public function updatePr($userId, UpdatePrRequest $request)
    {
        $input = $request->only([
            'favorite_skill',
            'experience_knowledge',
            'self_pr',
            'skills',
        ]);

        $data = UserService::getInstance()->updatePr($input, $userId);

        if ($data) {
            return $this->sendSuccessResponse([], trans('validation.INF.001'));
        }

        throw new InputException(trans('validation.ERR.007'));
    }

    public function updateMotivation($userId, UpdateMotivationRequest $request)
    {
        $inputs = $request->only(['motivation', 'noteworthy']);
        $data = UserService::getInstance()->updateMotivation($userId, $inputs);

        if ($data) {
            return $this->sendSuccessResponse([], trans('validation.INF.001'));
        }

        throw new InputException(trans('response.ERR.007'));
    }

    /**
     * List user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listInfoUser(Request $request)
    {
        $data = UserInfoTableService::getInstance()->data(
            null,
            null,
            $request->get('filters'),
            $request->get('per_page'),
        );

        return $this->sendSuccessResponse(new UserInfoCollection($data));
    }

    public function getAllOwner()
    {
        $data = UserService::getInstance()->getAllOwner();

        return $this->sendSuccessResponse($data);
    }
}
