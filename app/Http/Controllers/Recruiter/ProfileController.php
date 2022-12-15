<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recruiter\UpdateProfileRequest;
use App\Http\Resources\Recruiter\InformationResource;
use App\Services\Recruiter\ProfileService;

class ProfileController extends Controller
{
    private $profileService;
    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * update Information
     *
     * @param UpdateProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InputException
     */
    public function update(UpdateProfileRequest $request)
    {
        $input = $request->only([
            'first_name',
            'last_name',
            'furi_first_name',
            'furi_last_name',
            'company_name',
            'home_page_recruiter',
            'tel',
            'postal_code',
            'province_id',
            'province_city_id',
            'address',
            'building',
            'alias_name',
            'employee_quantity',
            'founded_year',
            'capital_stock',
            'manager_name',
            'line',
            'facebook',
            'instagram',
            'twitter',
        ]);

        $data = $this->profileService->withUser($this->guard()->user())->updateInformation($input);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('response.update_success'));
        }

        throw new InputException(trans('validation.ERR.006'));
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
