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
        $recruiter = $this->user;
        $matchingUserAnnounces= $recruiter->notifications()
            ->where('notice_type_id', Notification::TYPE_MATCHING_FAVORITE)
            ->where('be_announce', Notification::STATUS_NOT_ANNOUNCE)
            ->get();
        $matchingUserAnnounceIds = $matchingUserAnnounces->pluck('id')->toArray();
        $countMatching = $matchingUserAnnounces->count();
        $msg = '';

        if (!$countMatching) {
            return $msg;
        }

        foreach ($matchingUserAnnounces as $item) {
            $userIds[] = $item->noti_object_ids['user_id'];
        }

        $users = User::query()->roleUser()->whereIn('id', $userIds)->get();
        $honorifics = trans('notification.announcement.honorifics');

        if ($countMatching == 1) {
            $msg = $users->first()->getFullNameAttribute() .
                $honorifics .
                trans('notification.announcement.matching.one_person');
        }

        if ($countMatching > 1) {
            $users = $users->take(self::MAX_DISPLAY_USER_NAME);

            foreach ($users as $user) {
                $msg = $msg . $user->getFullNameAttribute() . $honorifics . '、';
            }

            $msg = rtrim($msg, '、');

            if ($countMatching > self::MAX_DISPLAY_USER_NAME) {
                $msg = $msg . trans('notification.announcement.amount_other', [
                        'amount' => $countMatching - self::MAX_DISPLAY_USER_NAME
                    ]);
            }

            $msg = $msg . trans('notification.announcement.matching.many_person');
        }

        Notification::query()->whereIn('id', $matchingUserAnnounceIds)
            ->update(['be_announce' => Notification::STATUS_ANNOUNCE]);

        return $msg;
    }
}
