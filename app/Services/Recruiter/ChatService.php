<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Models\Chat;
use App\Models\Notification;
use App\Models\Store;
use App\Models\User;
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ChatService extends Service
{
    /**
     * create chat
     *
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function store($data)
    {
        $user = User::roleUser()->where('id', $data['user_id']);
        $store = Store::query()->where([['id', $data['store_id']], ['user_id', $this->user->id]])->first();

        if (!$store || !$user) {
            throw new InputException(trans('validation.store_not_exist'));
        }

        try {
            DB::beginTransaction();

            $chat = Chat::create([
                'user_id' => $data['user_id'],
                'store_id' => $data['store_id'],
                'content' => $data['content'],
                'is_from_user' => Chat::FROM_USER['FALSE'],
                'be_readed' => Chat::UNREAD,
            ]);

            Notification::query()->create([
                'user_id' => $data['user_id'],
                'notice_type_id' => Notification::TYPE_NEW_MESSAGE,
                'noti_object_ids' => [
                    'store_id' => $data['store_id'],
                    'application_id' => null,
                    'user_id' => null,
                ],
                'title' => sprintf('%s%s', $store->name, trans('notification.new_message.N007.title')),
                'content' => sprintf('%s%s', $store->name, trans('notification.new_message.N007.content')),
            ]);

            DB::commit();

            return $chat;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);
            throw new InputException(trans('response.EXC.001'));
        }//end try
    }

    /**
     * list chat
     *
     * @param $store_id
     * @return mixed
     * @throws InputException
     */
    public function getChatListOfStore($store_id)
    {
        $store = Store::query()
            ->where([
                ['id', $store_id],
                ['user_id', $this->user->id]
            ])
            ->with('chats', function ($query) {
                $query->orderByDesc('created_at');
            })
            ->first();

        if ($store) {
             return $store->chats->unique('user_id');
        }

        throw new InputException(trans('response.not_found'));
    }
}
