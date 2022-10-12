<?php

namespace App\Models\Scopes;

use App\Models\JobPosting as ModelJobPosting;
use Carbon\Carbon;

trait JobPosting
{
    /**
     * Scope job release
     *
     * @param $query
     * @return mixed
     */
    protected function scopeReleased($query)
    {
        return $query->where('job_status_id', ModelJobPosting::STATUS_RELEASE);
    }

    /**
     * Scope job new
     *
     * @param $query
     * @return mixed
     */
    protected function scopeNew($query)
    {
        return $query->whereDate('released_at', '>=', Carbon::now()->subDays(config('validate.date_in_new_range')));
    }
}
