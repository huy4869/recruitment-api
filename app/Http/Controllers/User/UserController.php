<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends BaseController
{
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
        UserService::getInstance()->withUser($user)->update($inputs);

        return $this->sendSuccessResponse([], trans('response.update_base_info_success'));
    }
}
