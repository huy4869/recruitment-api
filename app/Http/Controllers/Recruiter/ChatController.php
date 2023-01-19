<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recruiter\ChatCreateRequest;
use App\Http\Resources\Recruiter\ChatListResourse;
use App\Http\Resources\Recruiter\ChatResource;
use App\Http\Resources\Recruiter\StoreNameResource;
use App\Services\Recruiter\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * @param $storeId
     * @return JsonResponse
     * @throws InputException
     */
    public function getChatListOfStore($storeId = null)
    {
        $data = $this->chatService->withUser($this->guard()->user())->getChatListOfStore($storeId);

        return $this->sendSuccessResponse(ChatListResourse::collection($data));
    }

    /**
     * creat chat
     *
     * @param ChatCreateRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(ChatCreateRequest $request)
    {
        $input = $request->only([
            'user_id',
            'store_id',
            'content',
        ]);

        $data = $this->chatService->withUser($this->guard()->user())->store($input);

        if ($data) {
            return $this->sendSuccessResponse(new ChatResource($data), trans('validation.INF.006'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }

    /**
     * @return JsonResponse
     */
    public function getListStore()
    {
        $data = $this->chatService->withUser($this->guard()->user())->getStoreWithRec();

        return $this->sendSuccessResponse(StoreNameResource::collection($data));
    }

    /**
     * @param Request $request
     * @param $store_id
     * @return JsonResponse
     * @throws InputException
     */
    public function getDetailChat(Request $request, $store_id)
    {
        $this->chatService->withUser($this->guard()->user())->updateBeReaded($store_id, $request->get('user_id'));
        $chatDetails = $this->chatService->withUser($this->guard()->user())->getDetailChat($store_id, $request->get('user_id'));

        return $this->sendSuccessResponse($chatDetails);
    }

    public function count()
    {
        $data = $this->chatService->withUser($this->guard()->user())->count();

        return $this->sendSuccessResponse($data);
    }
}
