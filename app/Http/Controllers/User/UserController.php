<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\UpdateInformationPrRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\InfoResource;
use App\Http\Resources\User\InformationPrResource;
use App\Http\Resources\User\MotivationResource;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends BaseController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Update user
     *
     * @param UserUpdateRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function update(UserUpdateRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only([
            'first_name',
            'last_name',
            'alias_name',
            'furi_first_name',
            'furi_last_name',
            'birthday',
            'age',
            'gender_id',
            'tel',
            'email',
            'line',
            'facebook',
            'instagram',
            'twitter',
            'postal_code',
            'province_id',
            'city',
            'address',
            'avatar',
            'images',
        ]);
        $this->userService->withUser($user)->update($inputs);

        return $this->sendSuccessResponse([], trans('response.update_base_info_success'));
    }

    /**
     * get basic info user
     *
     * @return JsonResponse
     */
    public function detail()
    {
        $user = $this->guard()->user();
        $user = $this->userService->withUser($user)->getBasicInfo();

        return $this->sendSuccessResponse(new InfoResource($user));
    }

    /**
     * get Information Pr
     *
     * @return JsonResponse
     */
    public function detailPr()
    {
        $user = $this->guard()->user();
        $user = $this->userService->withUser($user)->getPrInformation();

        return $this->sendSuccessResponse(new InformationPrResource($user));
    }

    /**
     * Update pr information
     *
     * @param UpdateInformationPrRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function updatePr(UpdateInformationPrRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only(['favorite_skill', 'experience_knowledge', 'self_pr']);
        $data = $this->userService->withUser($user)->updateInformationPr($inputs);

        if ($data) {
            return $this->sendSuccessResponse([], trans('response.update_success'));
        }

        throw new InputException(trans('validation.ERR.011'));
    }

    /**
     * Get motivation
     *
     * @return JsonResponse
     */
    public function detailMotivation()
    {
        $user = $this->guard()->user();

        return $this->sendSuccessResponse(new MotivationResource($user));
    }
}
