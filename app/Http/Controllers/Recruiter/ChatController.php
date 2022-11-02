<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recruiter\ChatCreateRequest;
use App\Http\Resources\Recruiter\ChatResource;
use App\Services\Recruiter\ChatService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    private $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
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
            return $this->sendSuccessResponse(new ChatResource($data), trans('response.INF.006'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }
}
