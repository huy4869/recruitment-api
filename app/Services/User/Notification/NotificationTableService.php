<?php

namespace App\Services\User\Notification;

use App\Models\Notification;
use App\Services\TableService;
use Illuminate\Database\Eloquent\Builder;

class NotificationTableService extends TableService
{
    /**
     * @return Builder
     */
    public function makeNewQuery()
    {
        return Notification::query()->where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->selectRaw($this->getSelectRaw());
    }

    /**
     * Get Select Raw
     *
     * @return string
     */
    protected function getSelectRaw()
    {
        return 'notifications.user_id,
            notifications.noti_object_ids,
            notifications.title,
            notifications.content,
            notifications.be_read,
            notifications.created_at';
    }
}
