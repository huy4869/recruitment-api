<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Resources\Recruiter\NotificationCollection;
use App\Services\Recruiter\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * total notification
     *
     * @return JsonResponse
     */
    public function count()
    {
        $data = $this->notificationService->withUser($this->guard()->user())->count();

        return $this->sendSuccessResponse(['count' => $data]);
    }

    /**
     * list notification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotify(Request $request)
    {
        $data = $this->notificationService->withUser($this->guard()->user())->getNotify($request->get('per_page'));

        return $this->sendSuccessResponse(new NotificationCollection($data));
    }

    /**
     * update be read
     *
     * @param $id
     * @return JsonResponse
     * @throws \App\Exceptions\InputException
     */
    public function updateBeReadNotify($id)
    {
        $data = $this->notificationService->withUser($this->guard()->user())->updateBeReadNotify($id);

        return $this->sendSuccessResponse($data);
    }

    /**
     * @return JsonResponse
     */
    public function matchingAnnouncement()
    {
        $recruiter = $this->guard()->user();
        $msg = $this->notificationService->withUser($recruiter)->makeMatchingAnnouncement();

        return $this->sendSuccessResponse($msg);
    }

    public function updateMatching()
    {
        $recruiter = $this->guard()->user();
        $msg = $this->notificationService->withUser($recruiter)->updateMatching();

        return $this->sendSuccessResponse($msg);
    }
}
