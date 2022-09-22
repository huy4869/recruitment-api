<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Traits\HasRateLimiter;
use App\Http\Requests\Admin\Auth\ChangePasswordRequest;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Requests\Admin\Auth\UpdateProfileRequest;
use App\Http\Resources\Admin\Auth\MeResource;
use App\Services\Admin\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    use HasRateLimiter;

    public const MAX_ATTEMPTS_LOGIN = 5;
    public const DECAY_SECONDS = 60;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware())->except(['login']);
        $this->middleware($this->guestMiddleware())->only(['login']);
    }

    /**
     * Login
     *
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function login(LoginRequest $request)
    {
        $ip = $request->ip();
        $inputs = $request->only(['email', 'password']);
        $key = Str::lower($inputs['email'] . '|user_login|' . $ip);
        if ($this->tooManyAttempts($key, self::MAX_ATTEMPTS_LOGIN)) {
            return $this->sendLockoutResponse($key);
        }

        $loginData = AuthService::getInstance()->login($inputs);
        if ($loginData) {
            $this->clearLoginAttempts($key);

            return $this->sendSuccessResponse($loginData);
        }

        $this->incrementAttempts($key, self::DECAY_SECONDS);
        if ($this->retriesLeft($key, self::MAX_ATTEMPTS_LOGIN) == 0) {
            throw new InputException(trans('auth.throttle', ['seconds' => self::DECAY_SECONDS]));
        }

        return $this->sendFailedLoginResponse();
    }

    /**
     * Send Failed Login Response
     *
     * @return JsonResponse
     */
    protected function sendFailedLoginResponse()
    {
        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_UNAUTHORIZED, trans('auth.failed'), null);
    }

    /**
     * Current login user
     *
     * @return JsonResponse
     */
    public function currentLoginUser()
    {
        $currentUser = $this->guard()->user();

        return $this->sendSuccessResponse(new MeResource($currentUser));
    }

    /**
     * Update profile
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $inputs = $request->only([
            'name',
        ]);
        $currentUser = $this->guard()->user();

        $data = AuthService::getInstance()->withUser($currentUser)->update($inputs);

        return $this->sendSuccessResponse($data, trans('response.update_successfully'));
    }

    /**
     * Change password
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $currentUser = $this->guard()->user();
        $inputs = $request->only(['current_password', 'password']);
        $data = AuthService::getInstance()->withUser($currentUser)->changePassword($inputs);

        return $this->sendSuccessResponse($data, trans('auth.logout_success'));
    }

    /**
     * Logout
     *
     * @return JsonResponse
     */
    public function logout()
    {
        $currentUser = $this->guard()->user();
        $currentUser->currentAccessToken()->delete();

        return $this->sendSuccessResponse(null, trans('auth.logout_success'));
    }
}
