<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Resources\User\ChatDetailResource;
use App\Http\Resources\User\ChatResource;
use App\Http\Requests\User\ChatCreateRequest;
use App\Services\User\ChatService;

class ChatController extends BaseController
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

    /**
     * create chat
     *
     * @param ChatCreateRequest $request
     * @param $store_id
     * @return \Illuminate\Http\JsonResponse
     * @throws InputException
     */
    public function store(ChatCreateRequest $request)
    {
        if ($request->store_id) {
            $input = $request->only([
                'store_id',
                'content'
            ]);
            $data = $this->chatService->withUser($this->guard()->user())->store($input);

            if ($data) {
                return $this->sendSuccessResponse($data, trans('response.INF.006'));
            }

            throw new InputException(trans('validation.ERR.006'));
        }

        throw new InputException(trans('validation.store_id_required'));
    }

    /**
     * total unread
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $data = $this->chatService->withUser($this->guard()->user())->unreadCount();

        return $this->sendSuccessResponse($data);
    }
}
