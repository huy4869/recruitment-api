<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\Chat;
use App\Models\Notification;
use App\Models\Store;
use App\Models\User;
use App\Services\Service;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
        $user = User::roleUser()->where('id', $data['user_id'])->first();
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
            throw new InputException(trans('validation.EXC.001'));
        }//end try
    }

    /**
     * list chat
     *
     * @param $storeId
     * @return Collection
     * @throws InputException
     */
    public function getChatListOfStore($storeId = null)
    {
        $stores = $this->user->stores()
            ->with([
                'chats' => function ($query) {
                    $query->where(function ($query) {
                        $query->where('is_apply_message', Chat::APPLY_MESSAGE['FROM_USER'])
                            ->orWhere('is_apply_message', null);
                        })
                        ->orderByDesc('created_at');
                },
                'chats.userTrashed',
                'chats.userTrashed.avatarBanner'
            ]);

        if ($storeId) {
            $stores->withTrashed()->where('id', $storeId);
            if (!$stores->first()) {
                throw new InputException(trans('response.not_found'));
            }

            if (!is_null($stores->first()->deleted_at)) {
                throw new InputException(trans('response.deleted_store'));
            }
        }

        if ($stores->count()) {
            $collectionStoreChat = collect();

            $stores->each(function ($store) use ($collectionStoreChat) {
                if ($store->chats->count()) {
                    $storeChat = $store->chats->unique('user_id');

                    foreach ($storeChat as $chat) {
                        $collectionStoreChat->push($chat);
                    }
                }
            });

            return $collectionStoreChat;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * get Store with Rec
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStoreWithRec()
    {
        return $this->user->stores()->get();
    }

    /**
     * get detail chat
     *
     * @param $store_id
     * @param $user_id
     * @return array
     * @throws InputException
     */
    public function getDetailChat($store_id, $user_id)
    {
        $rec = $this->user->id;
        $detailChats = Chat::query()->with('userTrashed')
            ->whereHas('store', function ($q) use ($store_id, $rec) {
                $q->where([
                    ['id', $store_id],
                    ['user_id', $rec]
                ]);
            })
            ->where([
                ['store_id', $store_id],
                ['user_id', $user_id]
            ])
            ->where(function ($query) {
                $query->where('is_apply_message', Chat::APPLY_MESSAGE['FROM_USER'])
                    ->orWhere('is_apply_message', null);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });

        $store = Store::withTrashed()->where([
            ['id', $store_id],
            ['user_id', $rec]
        ])->first();

        if (!$store) {
            throw new InputException(trans('response.not_found'));
        }

        if (!is_null($store->deleted_at)) {
            throw new InputException(trans('response.deleted_store'));
        }

        $result = [];
        $isDeleteUser = false;

        foreach ($detailChats as $key => $items) {
            $data = [];

            foreach ($items as $item) {
                $isDeleteUser = isset($item['userTrashed']['deleted_at']);
                if (is_null($item['content'])) {
                    continue;
                }

                $data[] = [
                    'first_name' => $item['userTrashed']['first_name'],
                    'last_name' => $item['userTrashed']['last_name'],
                    'avatar' => FileHelper::getFullUrl($item['userTrashed']['avatarBanner']['url'] ?? null),
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
            'is_delete_user' => $isDeleteUser,
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
     * @param $store_id
     * @param $user_id
     * @return int
     */
    public function updateBeReaded($store_id, $user_id)
    {
        $rec = $this->user->id;

        return Chat::query()
            ->whereHas('store', function ($q) use ($store_id, $rec) {
                $q->where([
                    ['id', $store_id],
                    ['user_id', $rec]]);
            })
            ->where([
                ['store_id', $store_id],
                ['user_id', $user_id],
                ['is_from_user', Chat::FROM_USER['TRUE']]
            ])
            ->update(['be_readed' => Chat::BE_READED]);
    }

    public function count()
    {
        $storeIds = $this->user->stores()->pluck('id')->toArray();
        $chat = Chat::where([
            ['be_readed', Chat::UNREAD],
            ['is_from_user', Chat::FROM_USER['TRUE']]
        ])->whereIn('store_id', $storeIds)->get()
            ->unique('store_id')
            ->count();

        return [
            'total_unread' => $chat,
        ];
    }
}
