<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recruiter\User\FavoriteRequest;
use App\Http\Resources\Recruiter\User\AppUserResource;
use App\Http\Resources\Recruiter\User\UserCollection;
use App\Http\Resources\Recruiter\User\UserResource;
use App\Models\FavoriteUser;
use App\Services\Recruiter\User\UserService;
use App\Services\Recruiter\User\UserTableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $recruiter = $this->guard()->user();
        $users = UserTableService::getInstance()->data(
            null,
            null,
            $request->get('filters'),
            $request->get('per_page')
        );

        return $this->sendSuccessResponse(new UserCollection([
            'recruiter' => $recruiter,
            'users' => $users,
        ]));
    }

    /**
     * @return JsonResponse
     */
    public function newUsers(Request $request)
    {
        $recruiter = $this->guard()->user();
        $userId = $request->get('user_id');

        if ($request->get('mode') == UserService::APP_MODE) {
            $users = UserService::getInstance()->withUser($recruiter)->getAppNewUser($userId);

            if ($users) {
                return $this->sendSuccessResponse(AppUserResource::collection($users));
            }
        } else {
            $users = UserService::getInstance()->withUser($recruiter)->getNewUsers();

            if ($users) {
                return $this->sendSuccessResponse(UserResource::collection($users));
            }
        }

        return $this->sendSuccessResponse([]);
    }

    /**
     * @return JsonResponse
     */
    public function suggestUsers(Request $request)
    {
        $recruiter = $this->guard()->user();
        $userId = $request->get('user_id');

        if ($request->get('mode') == UserService::APP_MODE) {
            $users = UserService::getInstance()->withUser($recruiter)->getAppSuggestUsers($userId);

            if ($users) {
                return $this->sendSuccessResponse(AppUserResource::collection($users));
            }
        } else {
            $users = UserService::getInstance()->withUser($recruiter)->getSuggestUsers();

            if ($users) {
                return $this->sendSuccessResponse(UserResource::collection($users));
            }
        }

        return $this->sendSuccessResponse([]);
    }

    /**
     * @param FavoriteRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function addOrRemoveFavoriteUser(FavoriteRequest $request)
    {
        $recruiter = $this->guard()->user();
        $inputs = $request->only([
            'user_id',
            'action_type'
        ]);

        if ($request->action_type == FavoriteUser::FAVORITE_USER) {
            $result = UserService::getInstance()->withUser($recruiter)->favoriteUser($inputs);
        }

        if ($request->action_type == FavoriteUser::UNFAVORITE_USER) {
            $result = UserService::getInstance()->withUser($recruiter)->unfavoriteUser($inputs);
        }

        if ($result) {
            if ($request->action_type == FavoriteUser::FAVORITE_USER) {
                $msg = trans('validation.INF.021');
            } else {
                $msg = trans('validation.INF.022');
            }

            return $this->sendSuccessResponse($result, $msg);
        }

        throw new InputException(trans('response.not_found'));
    }

    public function listFavorite(Request $request)
    {
        $users = UserTableService::getInstance()->data(
            null,
            null,
            null,
            $request->get('per_page')
        );

        return $this->sendSuccessResponse(new UserCollection([
            'recruiter' => $this->guard()->user(),
            'users' => $users,
        ]));
    }
}
