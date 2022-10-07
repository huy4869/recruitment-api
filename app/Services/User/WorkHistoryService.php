<?php

namespace App\Services\User;

use App\Models\UserWorkHistory;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class WorkHistoryService extends Service
{
    /**
     * List user work history
     *
     * @return Builder[]|Collection
     */
    public function list()
    {
        $user = $this->user;
        return UserWorkHistory::query()
            ->where('user_id', $user->id)
            ->with(['workType', 'jobType'])
            ->orderBy('period_end', 'DESC')
            ->get();
    }
}
