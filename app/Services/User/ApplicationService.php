<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\Application;
use App\Models\MInterviewApproach;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ApplicationService extends Service
{
    /**
     * List user applications
     *
     * @return Builder[]|Collection
     */
    public function list()
    {
        $dataApplications =  Application::query()
            ->with(['jobPosting', 'store', 'interviews', 'jobPosting.bannerImage'])
            ->where('user_id', $this->user->id)
            ->orderBy('date', 'DESC')
            ->get()
            ->toArray();

        $dataInterviewApproaches = MInterviewApproach::all()->pluck('name', 'id')->toArray();
        foreach ($dataApplications as $key => $application) {
            $interviewApproaches = json_decode($application['interview_approaches']);
            $dataApplications[$key]['interview_approaches_name'] = $interviewApproaches->approach;
            $dataApplications[$key]['interview_approaches_id'] = $interviewApproaches->id;
            $dataApplications[$key]['interview_approaches_status_name'] = $dataInterviewApproaches[$interviewApproaches->id];
        }

        return $dataApplications;
    }

    /**
     * List waiting interview
     *
     * @return array
     */
    public function getWaitingInterviews($all)
    {
        $userInterviews = $this->user->applications()
            ->whereHas('interviews', function ($query) {
                $query->where('id', Application::STATUS_WAITING_INTERVIEW);
            })
            ->orderBy('date', 'asc')
            ->get();

        $amountInterviews = $userInterviews->count();

        self::addInterviewActionDateInfo($userInterviews);

        if ($all) {
            $userInterviews->shift(config('application.waiting_interview_nearest_amount'));

            return [
                'interviews' => $userInterviews->all(),
                'view_all' => false,
            ];
        }

        return [
            'interviews' => $userInterviews->take(config('application.waiting_interview_nearest_amount')),
            'view_all' => $amountInterviews > config('application.waiting_interview_nearest_amount'),
        ];
    }

    /**
     * List applied
     *
     * @return array
     */
    public function getApplied($all)
    {
        $userInterviews = $this->user->applications()
            ->whereHas('interviews', function ($query) {
                $query->whereNot('id', Application::STATUS_CANCELED);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $amountInterviews = $userInterviews->count();

        self::addInterviewActionDateInfo($userInterviews);

        if ($all) {
            return [
                'interviews' => $userInterviews,
                'view_all' => false,
            ];
        }

        return [
            'interviews' => $userInterviews->take(config('application.application_newest_amount')),
            'view_all' => $amountInterviews > config('application.application_newest_amount'),
        ];
    }

    /**
     * Cancel applied
     *
     * @return bool
     * @throws InputException
     */
    public function cancelApplied($id)
    {
        $statusCanCancel = [
            Application::STATUS_APPLYING,
            Application::STATUS_WAITING_INTERVIEW,
            Application::STATUS_WAITING_RESULT,
        ];

        $application = Application::query()
            ->where('user_id', $this->user->id)
            ->where('id', $id)
            ->whereIn('interview_status_id', $statusCanCancel)
            ->first();

        if (!$application) {
            throw new InputException(trans('response.invalid'));
        }

        return $application->update([
            'interview_status_id' => Application::STATUS_CANCELED
        ]);
    }

    /**
     * @param $userInterviews
     * @return mixed
     */
    public static function addInterviewActionDateInfo($userInterviews)
    {
        $statusCanCancel = [
            Application::STATUS_APPLYING,
            Application::STATUS_WAITING_INTERVIEW,
            Application::STATUS_WAITING_RESULT,
        ];

        $statusCanChangeInterview = [
            Application::STATUS_APPLYING
        ];

        $today = now();
        $tomorrow = now()->addDays();
        $dayAfterTomorrow = now()->addDays(2);
        $dayAfterThreeDays = now()->addDays(3);

        foreach ($userInterviews as $interview) {
            $interviewStatus = $interview->interview_status_id;
            $interviewDate = Carbon::parse($interview->date);

            if ($today <= $interviewDate && $interviewDate < $tomorrow) {
                $interview->date_status = trans('common.today');
            }

            if ($tomorrow <= $interviewDate && $interviewDate < $dayAfterTomorrow) {
                $interview->date_status = trans('common.tomorrow');
            }

            if ($dayAfterTomorrow <= $interviewDate && $interviewDate < $dayAfterThreeDays) {
                $interview->date_status = trans('common.day_after_tomorrow');
            }

            $interview->can_cancel = !!in_array($interviewStatus, $statusCanCancel);
            $interview->can_change_interview = !!in_array($interviewStatus, $statusCanChangeInterview);
        }

        return $userInterviews;
    }
}
