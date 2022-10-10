<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\Chat;
use App\Models\Store;
use App\Services\Service;

class ChatService extends Service
{
    /**
     * list message
     * @return mixed
     */
    public function getChatList()
    {
        $user = $this->user;

        return Chat::with('store')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->unique('store_id');
    }

    /**
     * detail message
     *
     * @param $store_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDetail($store_id)
    {
        $user = $this->user;
        $detailMessage = Chat::with('store', 'user')
            ->where([['store_id', $store_id], ['user_id', $user->id]])
            ->orderByDesc('created_at')
            ->get();

        return $detailMessage;
    }

    /**
     * update read user
     *
     * @param $store_id
     * @return int
     */
    public function updateBeReaded($store_id)
    {
        $user = $this->user;

        return $user->chats()->where([
                ['store_id', $store_id],
                ['is_from_user', Chat::FROM_USER['FALSE']]
            ])
            ->update(['be_readed' => Chat::BE_READED]);
    }

    /**
     * create chat
     *
     * @param $data
     * @return mixed
     */
    public function store($data)
    {
        $store = Store::pluck('id')->toArray();
        if (in_array($data['store_id'], $store)) {
            return Chat::create([
                'user_id' => $this->user->id,
                'store_id' => $data['store_id'],
                'content' => $data['content'],
                'is_from_user' => Chat::FROM_USER['TRUE'],
                'be_readed' => Chat::UNREAD,
            ]);
        }

        throw new InputException(trans('validation.store_not_exist'));
    }

    /**
     * total unread
     *
     * @return array
     */
    public function unreadCount()
    {
        $chat = Chat::where([
                ['user_id', $this->user->id],
                ['is_from_user', Chat::FROM_USER['FALSE']],
                ['be_readed', Chat::UNREAD]
            ])
            ->select('store_id')
            ->groupBy('store_id')
            ->get();

        return [
            'total_unread' => $chat->count(),
        ];
    }
}
