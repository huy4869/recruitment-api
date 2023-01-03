<?php

namespace App\Models\Scopes;

use App\Models\MInterviewStatus;

trait Application
{
    protected function scopeNotAccept($query)
    {
        return $query->where('interview_status_id', '!=', MInterviewStatus::STATUS_ACCEPTED);
    }
}
