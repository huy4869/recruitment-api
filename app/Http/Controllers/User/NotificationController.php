<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\Notification\NotificationCollection;
use App\Services\User\Notification\NotificationService;
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

    /**
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function updateBeReadNotification($id)
    {
        $user = $this->guard()->user();
        $result = NotificationService::getInstance()->withUser($user)->updateBeReadNotification($id);

        return $this->sendSuccessResponse($result);
    }

    /**
     * count notifications
     *
     * @return JsonResponse
     */
    public function count()
    {
        $user = $this->guard()->user();
        $result = NotificationService::getInstance()->withUser($user)->count();

        return $this->sendSuccessResponse(['count' => $result]);
    }
}
