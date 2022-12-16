<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\UpdateInformationPrRequest;
use App\Http\Requests\User\UpdateMotivationRequest;
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
            'gender_id',
            'tel',
            'email',
            'line',
            'facebook',
            'instagram',
            'twitter',
            'postal_code',
            'province_id',
            'province_city_id',
            'building',
            'address',
            'avatar',
            'images',
            'is_public_avatar',
        ]);
        $this->userService->withUser($user)->update($inputs);

        return $this->sendSuccessResponse([], trans('validation.INF.001'));
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
        $inputs = $request->only(['favorite_skill', 'experience_knowledge', 'self_pr', 'skills']);
        $data = $this->userService->withUser($user)->updateInformationPr($inputs);

        if ($data) {
            return $this->sendSuccessResponse([], trans('validation.INF.001'));
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

    /**
     * Update motivation
     *
     * @param UpdateMotivationRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function updateMotivation(UpdateMotivationRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only(['motivation', 'noteworthy']);
        $data = $this->userService->withUser($user)->updateMotivation($inputs);

        if ($data) {
            return $this->sendSuccessResponse([], trans('validation.INF.001'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }
}
