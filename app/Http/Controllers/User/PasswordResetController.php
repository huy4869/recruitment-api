<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
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

        if ($data) {
            return $this->sendSuccessResponse($data, trans('response.send_mail_success'));
        }

        return ResponseHelper::sendResponse(
            ResponseHelper::STATUS_CODE_VALIDATE_ERROR,
            trans('response.invalid'),
            [
                'email' => [
                    trans('validation.COM.006', [
                        'attribute' => trans('validation.attributes.email')
                    ])
                ]
            ]
        );
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
        PasswordResetService::getInstance()->resetPassword($request->all());

        return $this->sendSuccessResponse([], trans('response.reset_password'));
    }
}
