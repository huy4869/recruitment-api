<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\UserProfileResource;
use App\Services\Recruiter\UserProfileService;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    private $userProfile;
    public function __construct(UserProfileService $userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * detail user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\InputException
     */
    public function detail(Request $request)
    {
        $data = $this->userProfile->detail($request->get('user_id'));

        return $this->sendSuccessResponse($data);
    }
}