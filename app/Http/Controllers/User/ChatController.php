<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\ChatDetailResource;
use App\Http\Resources\User\ChatResource;
use App\Services\User\ChatService;
use Carbon\Carbon;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * list chat
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = $this->chatService->withUser($this->guard()->user())->getChatList();

        return $this->sendSuccessResponse(ChatResource::collection($data));
    }

    /**
     * detail chat and update read user
     *
     * @param $store_id
     * @return \Illuminate\Http\JsonResponse
     * @throws InputException
     */
    public function detail($store_id)
    {
        $updateReaded = $this->chatService->withUser($this->guard()->user())->updateBeReaded($store_id);

        if ($updateReaded) {
            $data = $this->chatService->withUser($this->guard()->user())->getDetail($store_id);

            return $this->sendSuccessResponse(ChatDetailResource::collection($data));
        }

        throw new InputException(trans('response.readed_update_error'));
    }
}
