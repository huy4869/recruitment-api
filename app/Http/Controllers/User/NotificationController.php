<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Notification\NotificationCollection;
use App\Services\User\Notification\NotificationTableService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $user = $this->guard()->user();
        [$search, $orders, $filters, $perPage] = $this->convertRequest($request);
        $notifications = NotificationTableService::getInstance()->withUser($user)
            ->data($search, $orders, $filters, $perPage);

        return $this->sendSuccessResponse(new NotificationCollection($notifications));
    }
}
