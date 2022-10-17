<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Notification\NotificationCollection;
use App\Services\User\Notification\NotificationTableService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $notifications = NotificationTableService::getInstance()->withUser($user)
            ->data(null, null, null, config('paginate.notification.per_page'));

        return $this->sendSuccessResponse(new NotificationCollection($notifications));
    }
}
