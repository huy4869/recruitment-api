<?php

namespace App\Services\User\Notification;

use App\Exceptions\InputException;
use App\Models\Notification;
use App\Services\Service;

class NotificationService extends Service
{
    /**
     * @param $id
     * @return bool|int
     * @throws InputException
     */
    public function updateBeReadNotification($id)
    {
        $notification = $this->user->notifications()->where('id', $id)->first();

        if ($notification && $notification->be_read == Notification::STATUS_UNREAD) {
            return $notification->update([
                'be_read' => Notification::STATUS_READ
            ]);
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * count notifications
     *
     * @return int
     */
    public function count()
    {
        return $this->user->notifications()->where('be_read', Notification::STATUS_UNREAD)->count();
    }
}
