<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\InformationResource;
use App\Services\Recruiter\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private $profileService;
    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * get information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInformation()
    {
        $rec = $this->guard()->user();
        $data = $this->profileService->withUser($rec)->getInformation();

        return $this->sendSuccessResponse(InformationResource::collection($data));
    }
}
