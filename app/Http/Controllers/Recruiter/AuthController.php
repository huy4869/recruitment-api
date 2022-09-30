<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Traits\HasRateLimiter;
use App\Http\Requests\Recruiter\Auth\RegisterRequest;
use App\Helpers\ResponseHelper;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Services\Recruiter\AuthService;
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
        $this->middleware($this->guestMiddleware())->only(['login', 'register']);
        $this->middleware($this->authMiddleware())->except(['login', 'register']);
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
        $key = Str::lower($inputs['email'] . '|recruiter_login|' . $ip);

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
     * Register
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function register(RegisterRequest $request)
    {
        $input = $request->only([
            'email',
            'password',
            'password_confirmation'
        ]);

        $data = AuthService::getInstance()->register($input);

        return $this->sendSuccessResponse($data, trans('auth.register_success'));
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
