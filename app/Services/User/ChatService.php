<?php

namespace App\Services\User;

use App\Models\Chat;
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

        return Chat::with('store')->where('user_id', $user->id)->orderByDesc('created_at')->get()->unique('store_id');
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
        $detailMessage = Chat::with('store', 'user')->where([['store_id', $store_id], ['user_id', $user->id]])->orderByDesc('created_at')->get();

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

        return $user->chats()->where([['store_id', $store_id], ['is_from_user', Chat::FROM_USER['FALSE']]])->update(['be_readed' => Chat::BE_READED]);
    }
}
