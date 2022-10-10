<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\ForgotPassword\CheckTokenResetPasswordRequest;
use App\Http\Requests\Common\ForgotPassword\ResetPasswordRequest;
use App\Http\Requests\Common\ForgotPassword\SendMailRequest;
use App\Services\Recruiter\PasswordResetService;
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

        return $this->sendSuccessResponse($data, trans('response.send_mail_success'));
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