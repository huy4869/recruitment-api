<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Models\Notification;
use App\Services\Service;

class NotificationService extends Service
{
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
}
