<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMotivationRequest;
use App\Http\Requests\Admin\UpdatePrRequest;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Requests\Admin\User\UserInfoUpdateRequest;
use App\Http\Resources\Admin\DetailUserInfoResource;
use App\Http\Resources\Admin\User\DetailUserResource;
use App\Http\Resources\Admin\User\MotivationResource;
use App\Http\Resources\Admin\User\PrResource;
use App\Http\Resources\Admin\User\UserCollection;
use App\Http\Resources\Admin\UserInfoCollection;
use App\Models\User;
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
        $admin = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $data = UserTableService::getInstance()->withUser($admin)->data($search, $orders, $filters, $perPage);

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

        if (!$data) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
        }

        return ResponseHelper::sendResponse(
            is_null($data->deleted_at) ? ResponseHelper::STATUS_CODE_SUCCESS : ResponseHelper::STATUS_CODE_BAD_REQUEST,
            '',
            new DetailUserResource($data)
        );
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(StoreRequest $request)
    {
        $admin = $this->guard()->user();
        $inputs = $request->only([
            'role_id',
            'first_name',
            'last_name',
            'furi_first_name',
            'furi_last_name',
            'email',
            'password',
        ]);
        $data = UserService::getInstance()->withUser($admin)->store($inputs);

        $mes = trans('auth.register_success');

        if (in_array($data->role_id, [User::ROLE_USER, User::ROLE_RECRUITER])) {
            $mes = trans('validation.INF.025');
        }

        return $this->sendSuccessResponse($data, $mes);
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

        if (!$data) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
        }

        if (is_null($data['deleted_at'])) {
            return $this->sendSuccessResponse(new DetailUserInfoResource($data));
        }

        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, []);
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
            'is_public_avatar',
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

        throw new InputException(trans('validation.ERR.007'));
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

    public function detailPr($userId)
    {
        $data = UserService::getInstance()->getInfoUser($userId);

        return $this->sendSuccessResponse(new PrResource($data));
    }

    public function detailMotivation($userId)
    {
        $data = UserService::getInstance()->getInfoUser($userId);

        return $this->sendSuccessResponse(new MotivationResource($data));
    }

    public function availableActionRoles()
    {
        $admin = $this->guard()->user();
        $data = UserService::getInstance()->withUser($admin)->getAvailableActionRoles();

        return $this->sendSuccessResponse($data);
    }
}
