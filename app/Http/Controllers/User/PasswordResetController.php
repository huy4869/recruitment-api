<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Services\User\PasswordResetService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Common\ForgotPassword\CheckTokenResetPasswordRequest;
use App\Http\Requests\Common\ForgotPassword\ResetPasswordRequest;
use App\Http\Requests\Common\ForgotPassword\SendMailRequest;

class PasswordResetController extends BaseController
{
    /**
     * Send Mail Forgot Password
     * @param SendMailRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function sendMail(SendMailRequest $request)
    {
        $data = PasswordResetService::getInstance()->sendMail($request->get('email'));

        return $this->sendSuccessResponse($data);
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
        $token = PasswordResetService::getInstance()->checkToken($request->get('token'));

        if (!$token) {
            throw new InputException(trans('response.invalid_token'));
        }

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
        PasswordResetService::getInstance()->resetPassword($request->all());

        return $this->sendSuccessResponse([], trans('response.reset_password'));
    }
}
