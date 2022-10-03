<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\ForgotPassword\SendMailRequest;
use App\Services\User\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends BaseController
{
    /**
     * Send Mail Forgot Password
     * @param SendMailRequest $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws InputException
     */
    public function sendMail(SendMailRequest $request)
    {
        $data = PasswordResetService::getInstance()->sendMail($request->get('email'));

        return $this->sendSuccessResponse($data);
    }
}
