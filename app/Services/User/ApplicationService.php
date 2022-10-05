<?php

namespace App\Services\User;

use App\Models\Application;
use App\Services\Service;
use Illuminate\Database\Eloquent\Collection;

class ApplicationService extends Service
{
    /**
     * List waiting interview
     *
     * @return Collection
     */
    public function getWaitingInterviews($all)
    {
        $userInterviews = $this->user->applications()
            ->whereHas('interview', function ($query) {
                $query->where('id', Application::STATUS_WAITING_INTERVIEW);
            })
            ->orderBy('date', 'asc');

        if ($all) {
            return $userInterviews->get();
        }

        return $userInterviews->take(config('application.waiting_interview_nearest_amount'))->get();
    }
}
