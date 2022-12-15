<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Traits\HasRateLimiter;
use App\Http\Requests\Recruiter\Auth\RegisterRequest;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Recruiter\Auth\LoginRequest;
use App\Http\Requests\Recruiter\ChangePasswordRequest;
use App\Http\Resources\Recruiter\MeResource;
use App\Services\Recruiter\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $this->middleware($this->guestMiddleware())->only(['login', 'register', 'verifyRegister']);
        $this->middleware($this->authMiddleware())->except(['login', 'register', 'verifyRegister']);
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
            'first_name',
            'last_name',
            'furi_first_name',
            'furi_last_name',
            'email',
            'password',
            'password_confirmation'
        ]);

        $data = AuthService::getInstance()->register($input);

        return $this->sendSuccessResponse($data, trans('validation.INF.025'));
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

    /**
     * Verify register
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function verifyRegister(Request $request)
    {
        $token = $request->get('token');
        $data = AuthService::getInstance()->verifyRegister($token);

        return $this->sendSuccessResponse($data, __('auth.verify_register_success'));
    }

    /**
     * Current rec login
     * @return JsonResponse
     */
    public function me()
    {
        $data = $this->guard()->user();

        return $this->sendSuccessResponse(new MeResource($data));
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
        $input = $request->only([
            'current_password',
            'password'
        ]);
        $data = AuthService::getInstance()->withUser($currentUser)->changePassword($input);

        if ($data) {
            return $this->sendSuccessResponse([], trans('auth.update_success'));
        }

        throw new InputException(trans('validation.ERR.010'));
    }
}
