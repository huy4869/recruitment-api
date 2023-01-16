<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Models\Notification;
use App\Models\User;
use App\Services\Service;

class NotificationService extends Service
{
    const MAX_DISPLAY_USER_NAME = 3;

    /**
     * total notification
     *
     * @return int
     */
    public function count()
    {
        return $this->user->notifications()->where('be_read', Notification::STATUS_UNREAD)->count();
    }

    /**
     * list notification
     *
     * @param $per_page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNotify($per_page)
    {
        $limit = $per_page ?: config('paginate.notification.rec.per_page');

        return $this->user->notifications()
            ->orderByDesc('created_at')
            ->paginate($limit);
    }

    public function updateBeReadNotify($id)
    {
        $notify = $this->user->notifications()->where('id', $id)->update(['be_read' => Notification::STATUS_READ]);

        if ($notify) {
            return true;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * @return string
     */
    public function makeMatchingAnnouncement()
    {
        $recruiterId = $this->user->id;
        $matching = Notification::where(function ($q) use ($recruiterId) {
            $q->where(function ($query) use ($recruiterId) {
                $query->where('user_id', $recruiterId);
            })
                ->orWhere(function ($query) use ($recruiterId) {
                    $query->whereJsonContains('noti_object_ids->user_id', $recruiterId);
                });
        })
            ->where('notice_type_id', Notification::TYPE_MATCHING_FAVORITE)
            ->where('be_announce', Notification::STATUS_NOT_ANNOUNCE)
            ->get();
        $msg = '';
        $name = '';
        $honorifics = trans('notification.announcement.honorifics');
        $countMatching = $matching->count();
        if (!$countMatching) {
            return $msg;
        }

        foreach ($matching as $item) {
            if ($item->user_id === $recruiterId) {
                $name .= User::find($item->noti_object_ids['user_id'])->getFullNameAttribute() . $honorifics . '、';
            }

            if (substr_count($name, '、') > 2) {
                break;
            }
        }

        $amount = $countMatching/2 - self::MAX_DISPLAY_USER_NAME;

        if (substr_count($name, '、') == 1) {
            $name = rtrim($name, '、');
            $msg = $name . trans('notification.announcement.matching.one_person');
        }

        if (substr_count($name, '、') >= 2 && ($amount == 0 || $amount == -1)) {
            $name = rtrim($name, '、');
            $msg = $name . trans('notification.announcement.matching.two_person');
        }

        if (substr_count($name, '、') > 2 && $amount > 0) {
            $name = rtrim($name, '、');
            $msg = $name . trans('notification.announcement.amount_other', [
                    'amount' => $amount
                ]) . trans('notification.announcement.matching.many_person');
        }

        return $msg;
    }

    public function updateMatching()
    {
        $recruiterId = $this->user->id;
        $matchingIds = Notification::where(function ($q) use ($recruiterId) {
            $q->where(function ($query) use ($recruiterId) {
                $query->where('user_id', $recruiterId);
            })
                ->orWhere(function ($query) use ($recruiterId) {
                    $query->whereJsonContains('noti_object_ids->user_id', $recruiterId);
                });
        })
            ->where('notice_type_id', Notification::TYPE_MATCHING_FAVORITE)
            ->where('be_announce', Notification::STATUS_NOT_ANNOUNCE)
            ->pluck('id')->toArray();

        return Notification::query()->whereIn('id', $matchingIds)
            ->update(['be_announce' => Notification::STATUS_ANNOUNCE]);
    }
}
