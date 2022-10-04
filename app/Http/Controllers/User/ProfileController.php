<?php

namespace App\Http\Controllers\User;


use App\Services\User\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends BaseController
{
    /**
     * InformationController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware());
    }

    /**
     * get % Profile
     *
     * @return JsonResponse
     */
    public function getCompletionPercent()
    {
        $user = $this->guard()->user();

        $data = ProfileService::getInstance()->withUser($user)->getCompletionPercent();

        return $this->sendSuccessResponse($data);
    }
}
