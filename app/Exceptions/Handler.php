<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        InputException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (InputException $e, $request) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, $e->getMessage());
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, trans('response.not_found'));
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_BAD_REQUEST, trans('response.not_found'));
        });
    }

    /**
     * @param Request $request
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return ResponseHelper::sendResponse($exception->status, trans('response.invalid'), $exception->errors());
    }

    /**
     * Unauthenticated
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_UNAUTHORIZED, trans('response.unauthenticated'));
    }
}
