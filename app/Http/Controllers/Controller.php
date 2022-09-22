<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the guest middleware for the application.
     *
     * @return string
     */
    public function guestMiddleware()
    {
        $guard = $this->getGuard();
        return $guard ? ('guest:' . $guard) : 'guest';
    }

    /**
     * Get the auth middleware for the application.
     *
     * @return string
     */
    public function authMiddleware()
    {
        $guard = $this->getGuard();
        return $guard ? ('auth:' . $guard) : 'auth';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return string
     */
    protected function getGuard()
    {
        return property_exists($this, 'guard') ? $this->guard : config('auth.defaults.guard');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard($this->getGuard());
    }

    /**
     * Send Error Response
     *
     * @param string $message
     * @param null $errors
     * @param null $data
     * @param integer $code
     * @return JsonResponse
     */
    protected function sendErrorResponse(string $message, $errors = null, $data = null, $code = ResponseHelper::STATUS_CODE_BAD_REQUEST)
    {
        return ResponseHelper::sendResponse($code, $message, $data, $errors);
    }

    /**
     * @param $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function sendSuccessResponse($data, $message = '', $code = ResponseHelper::STATUS_CODE_SUCCESS)
    {
        return ResponseHelper::sendResponse($code, $message, $data);
    }

    /**
     * @param $request
     * @return array
     */
    protected function convertRequest($request)
    {
        return [
            $request->get('search'),
            $request->get('orders'),
            $request->get('filters'),
            $request->get('per_page'),
        ];
    }
}
