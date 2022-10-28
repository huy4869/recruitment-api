<?php

namespace App\Services\User\SearchJob;

use App\Models\SearchJob;
use App\Services\TableService;
use Illuminate\Database\Eloquent\Builder;

class SearchJobTableService extends TableService
{
    /**
     * @return Builder
     */
    public function makeNewQuery()
    {
        return SearchJob::query()->where('user_id', $this->user->id)
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
        return 'search_jobs.id,
            search_jobs.user_id,
            search_jobs.content,
            search_jobs.created_at';
    }
}
