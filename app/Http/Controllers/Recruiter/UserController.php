<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\User\UserCollection;
use App\Http\Resources\Recruiter\User\UserResource;
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
    public function newUsers()
    {
        $recruiter = $this->guard()->user();
        $users = UserService::getInstance()->withUser($recruiter)->getNewUsers();

        return $this->sendSuccessResponse(UserResource::collection($users));
    }

    /**
     * @return JsonResponse
     */
    public function suggestUsers()
    {
        $recruiter = $this->guard()->user();
        $users = UserService::getInstance()->withUser($recruiter)->getSuggestUsers();

        return $this->sendSuccessResponse(UserResource::collection($users));
    }
}
