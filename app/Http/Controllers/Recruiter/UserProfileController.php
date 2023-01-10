<?php

namespace App\Http\Controllers\Recruiter;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\ProfileUserResource;
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
    public function detail($id)
    {
        $data = $this->userProfile->detail($id);

        if (!$data) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_NOTFOUND, []);
        }

        return ResponseHelper::sendResponse(
            is_null($data['deleted_at']) ? ResponseHelper::STATUS_CODE_SUCCESS : ResponseHelper::STATUS_CODE_BAD_REQUEST,
            '',
            new ProfileUserResource($data)
        );
    }
}
