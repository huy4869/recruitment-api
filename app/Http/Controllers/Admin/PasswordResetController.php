<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ForgotPassword\SendMailRequest;
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

        return $this->sendSuccessResponse($data);
    }
}
