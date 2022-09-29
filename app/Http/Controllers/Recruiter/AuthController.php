<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Traits\HasRateLimiter;
use App\Http\Controllers\User\BaseController;
use App\Http\Requests\Recruiter\Auth\RegisterRequest;
use App\Services\Recruiter\AuthService;
use Illuminate\Http\JsonResponse;

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
        $this->middleware($this->guestMiddleware())->only(['register']);
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
}
