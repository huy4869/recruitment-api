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
     * @return array
     */
    public function getWaitingInterviews($all)
    {
        $userInterviews = $this->user->applications()
            ->whereHas('interview', function ($query) {
                $query->where('id', Application::STATUS_WAITING_INTERVIEW);
            })
            ->orderBy('date', 'asc')
            ->get();

        if ($all) {
            $userInterviews->shift(config('application.waiting_interview_nearest_amount'));

            return $userInterviews->all();
        }

        return $userInterviews->take(config('application.waiting_interview_nearest_amount'));
    }

    /**
     * List applied
     *
     * @return Collection
     */
    public function getApplied($all)
    {
        $userInterviews = $this->user->applications()
            ->whereHas('interview', function ($query) {
                $query->whereNot('id', Application::STATUS_CANCELED);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($all) {
            return $userInterviews;
        }

        return $userInterviews->take(config('application.application_newest_amount'));
    }

    /**
     * Cancel applied
     *
     * @return bool
     */
    public function cancelApplied($applicationId)
    {
        $application = Application::query()->where('id', $applicationId)->first();

        return $application->update([
            'interview_status_id' => Application::STATUS_CANCELED
        ]);
    }
}
