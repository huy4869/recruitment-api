<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Chat;
use App\Models\Notification;
use App\Models\Store;
use App\Services\Service;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatService extends Service
{
    /**
     * list message
     * @return mixed
     */
    public function getChatList()
    {
        $user = $this->user;

        return Chat::with([
                'storeTrashed',
                'storeTrashed.storeBanner'
            ])
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('is_apply_message', Chat::APPLY_MESSAGE['FROM_REC'])
                    ->orWhere('is_apply_message', null);
            })
            ->orderByDesc('created_at')
            ->get()
            ->unique('store_id');
    }

    /**
     * detail message
     *
     * @param $store_id
     * @return array
     */
    public function getDetail($store_id)
    {
        $user = $this->user;
        $detailChats = Chat::with([
                'storeTrashed',
                'storeTrashed.storeBanner',
                'user'
            ])
            ->where([
                ['store_id', $store_id],
                ['user_id', $user->id]
            ])
            ->where(function ($query) {
                $query->where('is_apply_message', Chat::APPLY_MESSAGE['FROM_REC'])
                    ->orWhere('is_apply_message', null);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });

        $result = [];
        $isDeleteStore = false;

        foreach ($detailChats as $key => $items) {
            $data = [];

            foreach ($items as $item) {
                $isDeleteStore = isset($item['storeTrashed']['deleted_at']);
                if (is_null($item['content'])) {
                    continue;
                }

                $data[] = [
                    'store_name' => $item['storeTrashed']['name'],
                    'store_banner' => FileHelper::getFullUrl($item['storeTrashed']['storeBanner']['url'] ?? null),
                    'send_time' => DateTimeHelper::formatTimeChat($item['created_at']),
                    'initial_time' => DateTimeHelper::formatDateTimeJa($item['created_at']),
                    'content' => $item['content'],
                    'is_from_user' => $item['is_from_user'],
                    'be_readed' => $item['be_readed'],
                ];
            }

            if (empty($data)) {
                continue;
            }

            $result[$this->checkDate($key)] = $data;
        }//end foreach

        return [
            'is_delete_store' => $isDeleteStore,
            'data' => $result,
        ];
    }

    /**
     * @param $dateTime
     * @return array|Application|Translator|string|null
     */
    public function checkDate($dateTime)
    {
        $now = Carbon::now()->format(config('date.fe_date_format'));
        $date = DateTimeHelper::formatDate($dateTime);
        if ($date == $now) {
            return trans('common.today');
        }

        return Carbon::parse($dateTime)->format(config('date.month_day'));
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
        $store = Store::where('id', $data['store_id'])->first();

        if (!$store) {
            throw new InputException(trans('validation.store_not_exist'));
        }

        try {
            DB::beginTransaction();

            $chat = Chat::create([
                'user_id' => $this->user->id,
                'store_id' => $data['store_id'],
                'content' => $data['content'],
                'is_from_user' => Chat::FROM_USER['TRUE'],
                'be_readed' => Chat::UNREAD,
            ]);

            Notification::query()->create([
                'user_id' => $store->user_id,
                'notice_type_id' => Notification::TYPE_NEW_MESSAGE,
                'noti_object_ids' => [
                    'store_id' => $data['store_id'],
                    'application_id' => null,
                    'user_id' => $this->user->id,
                ],
                'title' => sprintf('%s%s', $this->user->fullName, trans('notification.new_message.N002.title')),
                'content' => trans('notification.new_message.N002.content'),
            ]);

            DB::commit();

            return $chat;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);

            throw new InputException(trans('validation.EXC.001'));
        }//end try
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
