<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\MInterviewApproach;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ApplicationService extends Service
{
    /**
     * List user applications
     *
     * @return Builder[]|Collection
     */
    public function list()
    {
        return  Application::query()
            ->with(['jobPosting', 'store', 'interviews', 'jobPosting.bannerImage'])
            ->where('user_id', $this->user->id)
            ->orderBy('date', 'DESC')
            ->get();
    }

    /**
     * get interview approach
     *
     * @return array
     */
    public static function interviewApproach()
    {
         return $dataInterviewApproaches = MInterviewApproach::all()->pluck('name', 'id')->toArray();
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
<<<<<<< HEAD
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

    /**
     * Application
     *
     * @param $applicationId
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($applicationId)
    {
        $application = Application::query()
            ->where('user_id', '=', $this->user->id)
            ->where('id', '=', $applicationId)
            ->first();

        if ($application) {
            return $application;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * Detail Application and times
     *
     * @param $application
     * @param $times
     * @return array
     */
    public function detailApplicationAndTimes($application, $times)
    {
        $datePast = [];

        if ($application->date < now()->format('Y-m-d')) {
            $datePast = $this->appendCurrentApplicationTime($application->date, $application->hours);
        }

        return [
            'application_user' => [
                'date' => explode(' ', $application->date)[0],
                'hours' => $application->hours,
                'note' => $application->note,
                'interview_status' => [
                    'id' => $application->interview_status_id,
                    'name' => $application->interviews->name,
                ],
            ],
            'list_time' => array_merge($datePast, $times)
        ];
    }

    /**
     * Time check
     *
     * @param $date
     * @param $hours
     * @return array
     */
    public function appendCurrentApplicationTime($date, $hours)
    {
        $data = [];
        $dataPast = [];

        foreach (config('date.time') as $time) {
            $data[] = [
                'hours' => $time,
                'is_enabled_time' => $hours == $time,
            ];
        }

        $dataPast[] = [
            'date' => $date,
            'date_format' => DateTimeHelper::formatDateDayOfWeekJa($date),
            'is_enable' => true,
            'times' => $data
        ];

        return $dataPast;
    }

    /**
     * User update application
     *
     * @param $applicationId
     * @param $data
     * @return int
     * @throws InputException
     */
    public function updateApplication($applicationId, $data)
    {
        $user = $this->user;
        if (!in_array($data['hours'], config('date.time'))) {
            throw new InputException(trans('response.ERR.999'));
        }

        $application = Application::query()
            ->with('store.createdBy.recruiterOffTimes')
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $applicationId)
            ->first();

        if (!$application) {
            throw new InputException(trans('response.not_found'));
        }

        $date = $data['date'];
        $hours = $data['hours'];
        $now = now()->format('Y-m-d');
        $dateApplication = @explode(' ', $application->date)[0];
        $hoursApplication = $application->hours;

        if ($application->interview_status_id != Application::STATUS_APPLYING ||
            ($date == $dateApplication && $date < $now && $hours != $hoursApplication)) {
            throw new InputException(trans('response.not_found'));
        }

        $data = $this->saveMakeData($data);

        if ($date == $dateApplication && $hours == $hoursApplication) {
            return $application->update($data);
        }

        if ($date == $now && $this->checkTimeUpdate($hours)) {
            throw new InputException(trans('response.ERR.999'));
        }

        $applications = Application::query()
            ->whereIn('interview_status_id', [Application::STATUS_APPLYING, Application::STATUS_WAITING_INTERVIEW])
            ->where('id', '!=', $applicationId)
            ->whereDate('date', $date)
            ->where('hours', '=', $hours)
            ->get()
            ->pluck('user_id', 'job_posting_id')
            ->toArray();

        if (in_array($user->id, $applications) || in_array($application->job_posting_id, array_keys($applications))) {
            throw new InputException(trans('response.ERR.999'));
        }

        $month = Carbon::parse($data['date'])->firstOfMonth()->format('Y-m-d');
        $recruiterOffTimes = $application->store->createdBy->recruiterOffTimes->off_times ?? [];
        $recruiterOffTimes = JobPostingService::resultRecruiterOffTimes([$month], $recruiterOffTimes);
        $dataHours = preg_grep('/' . $date . '/i', $recruiterOffTimes);

        foreach ($dataHours as $dataHour) {
            if ($hours == explode(' ', $dataHour)[1]) {
                throw new InputException(trans('response.ERR.999'));
            }
        }

        return $application->update($data);
    }

    /**
     * @param $hours
     * @return false
     */
    public function checkTimeUpdate($hours)
    {
        $checkTime = array_search(DateTimeHelper::getTime(), config('date.time'));
        $checkTime = config('date.range_time') + ($checkTime ? $checkTime : 0);

        foreach (config('date.time') as $key => $time) {
            if ($key < $checkTime && $hours == $time) {
                return false;
            }
        }

        return true;
    }

    /**
     * Save make data
     *
     * @param $data
     * @return array
     * @throws InputException
     */
    public function saveMakeData($data)
    {
        $interviewApproaches = MInterviewApproach::query()->where('id', $data['interview_approaches_id'])->first();

        if (!$interviewApproaches) {
            throw new InputException('response.not_found');
        }

        return [
            'interview_approaches' => [
                'id' => $interviewApproaches->id,
                'approach' => $data['note'],
            ],
            'date' => $data['date'],
            'hours' => $data['hours'],
            'note' => $data['note'],
            'update_times' => now(),
        ];
    }
}
