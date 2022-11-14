<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\ForgotPassword\CheckTokenResetPasswordRequest;
use App\Http\Requests\Common\ForgotPassword\ResetPasswordRequest;
use App\Http\Requests\Common\ForgotPassword\SendMailRequest;
use App\Services\Admin\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send Mail Forgot Password
     *
     * @param SendMailRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws InputException
     */
    public function sendMail(SendMailRequest $request)
    {
        $data = PasswordResetService::getInstance()->sendMail($request->get('email'));

        return $this->sendSuccessResponse($data, trans('validation.INF.007'));
    }

    /**
     * Check token reset password
     *
     * @param CheckTokenResetPasswordRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function checkToken(CheckTokenResetPasswordRequest $request)
    {
        PasswordResetService::getInstance()->checkToken($request->get('token'));

        return $this->sendSuccessResponse([]);
    }

    /**
     * forgot password
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        PasswordResetService::getInstance()->resetPassword($request->only([
            'token',
            'password',
            'password_confirmation',
        ]));

        return $this->sendSuccessResponse([], trans('response.reset_password'));
    }
}
