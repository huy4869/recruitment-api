<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Http\Resources\User\ChatDetailResource;
use App\Http\Resources\User\ChatResource;
use App\Http\Requests\User\ChatCreateRequest;
use App\Services\User\ChatService;
use Carbon\Carbon;

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
            $result = [];
            $isCheck = true;
            $dateShow = '';

            foreach ($data as $value) {
                $detail = new ChatDetailResource($value);

                if ($isCheck && Carbon::now()->format('Y-m-d') > date('Y-m-d', strtotime($value->created_at))) {
                    $result[] = [
                        'is_date_now' => true,
                        'date_show' => $dateShow
                    ];
                    $isCheck = false;
                }
                $dateShow = DateTimeHelper::formatDateJa($value->created_at);
                $result[] = $detail;
            }

            return $this->sendSuccessResponse($result);
        }//end if

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
