<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Traits\HasRateLimiter;
use App\Http\Requests\User\Auth\ChangePasswordRequest;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Http\Requests\User\Auth\RegisterRequest;
use App\Http\Requests\User\Auth\UpdateProfileRequest;
use App\Http\Resources\User\Auth\MeResource;
use App\Services\User\AuthService;
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
        $this->middleware($this->authMiddleware())->except(['login', 'register']);
        $this->middleware($this->guestMiddleware())->only(['login', 'register']);
    }

    /**
     * Register
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function register(RegisterRequest $request)
    {
        $inputs = $request->only([
            'email',
            'password',
            'password_confirmation',
        ]);
        $data = AuthService::getInstance()->register($inputs);

        return $this->sendSuccessResponse($data, trans('auth.register_success'));
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
        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_UNAUTHORIZED, trans('auth.failed'));
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
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $currentUser = $this->guard()->user();
        $data = AuthService::getInstance()->withUser($currentUser)->changePassword($request->only('password'));

        if ($data) {
            return $this->sendSuccessResponse([], trans('auth.update_success'));
        }

        throw new InputException(trans('validation.ERR.010'));
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
