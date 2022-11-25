<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\MInterviewApproach;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use App\Services\Service;
use App\Services\User\Job\JobService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

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
            ->with(['jobPostingAcceptTrashed', 'storeAcceptTrashed', 'storeAcceptTrashed.owner', 'interviews', 'jobPostingAcceptTrashed.bannerImageAcceptTrashed'])
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'DESC')
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
                $query->where('id', MInterviewStatus::STATUS_WAITING_INTERVIEW);
            })
            ->with([
                'store',
                'store.owner',
                'jobPosting',
                'jobPosting.province',
                'jobPosting.provinceCity',
                'interviewApproach'
            ])
            ->orderBy('date', 'asc')
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
                $query->whereNot('id', MInterviewStatus::STATUS_CANCELED);
            })
            ->with([
                'storeAcceptTrashed',
                'storeAcceptTrashed.owner',
                'jobPostingAcceptTrashed',
                'jobPostingAcceptTrashed.province',
                'jobPostingAcceptTrashed.provinceCity',
                'interviewApproach'
            ])
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
            MInterviewStatus::STATUS_APPLYING,
            MInterviewStatus::STATUS_WAITING_INTERVIEW,
            MInterviewStatus::STATUS_WAITING_RESULT,
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
            'interview_status_id' => MInterviewStatus::STATUS_CANCELED
        ]);
    }

    /**
     * @param $userInterviews
     * @return mixed
     */
    public static function addInterviewActionDateInfo($userInterviews)
    {
        $statusCanCancel = [
            MInterviewStatus::STATUS_APPLYING,
            MInterviewStatus::STATUS_WAITING_INTERVIEW,
            MInterviewStatus::STATUS_WAITING_RESULT,
        ];

        $statusCanChangeInterview = [
            MInterviewStatus::STATUS_APPLYING
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
                'interview_approaches' => [
                    'id' => $application->interview_approach_id,
                    'approach' => $application->note,
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
                'is_enabled_time' => $hours == $time ? 1 : 0,
            ];
        }

        $dataPast[] = [
            'date' => explode(' ', $date)[0],
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
     * @throws InputException|ValidationException
     */
    public function updateApplication($applicationId, $data)
    {
        $user = $this->user;
        if (!in_array($data['hours'], config('date.time'))) {
            throw new InputException(trans('response.ERR.999'));
        }

        $application = Application::query()
            ->with(['store.owner.recruiterOffTimes', 'store.owner.stores.applications'])
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $applicationId)
            ->first();

        if (!$application) {
            throw new InputException(trans('response.not_found'));
        }

        $date = $data['date'];
        $hours = $data['hours'];
        $now = now()->format('Y-m-d');
        $dateApplication = explode(' ', $application->date)[0];
        $hoursApplication = $application->hours;

        if ($application->interview_status_id != MInterviewStatus::STATUS_APPLYING) {
            throw new InputException(trans('response.not_found'));
        }

        if (($date == $now && $this->checkTimeUpdate($hours))
            || ($date == $dateApplication && $date < $now && $hours != $hoursApplication)
            || ($date != $dateApplication && $date < $now)) {
            throw ValidationException::withMessages([
                'date' => trans('validation.ERR.037')
            ]);
        }

        $data = $this->saveMakeData($data);

        if ($date == $dateApplication && $hours == $hoursApplication) {
            return $this->userUpdateApplication($application, $data);
        }

        $applications = Application::query()
            ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW])
            ->where('id', '!=', $applicationId)
            ->whereDate('date', $date)
            ->where('hours', '=', $hours)
            ->get()
            ->pluck('user_id', 'job_posting_id')
            ->toArray();

        if (in_array($user->id, $applications) || in_array($application->job_posting_id, array_keys($applications))) {
            throw new InputException(trans('validation.ERR.036'));
        }

        $stores = $application->store->owner->stores;
        $recruiterApplications = collect();

        foreach ($stores as $store) {
            $recruiterApplications = $recruiterApplications->merge($store->applications);
        }

        foreach ($recruiterApplications as $recruiterApplication) {
            if (explode(' ', $recruiterApplication->date)[0] == $date
                && $recruiterApplication->hours == $hours
                && $recruiterApplication->id != $application->job_posting_id
                && in_array($recruiterApplication->interview_status_id, [
                   MInterviewStatus::STATUS_APPLYING,
                    MInterviewStatus::STATUS_WAITING_INTERVIEW
                ])) {
                throw new InputException(trans('validation.ERR.036'));
            }
        }

        $month = Carbon::parse($data['date'])->firstOfMonth()->format('Y-m-d');
        $recruiterOffTimes = $application->store->owner->recruiterOffTimes->off_times ?? [];
        $recruiterOffTimes = JobService::resultRecruiterOffTimes([$month], $recruiterOffTimes);
        $dataHours = preg_grep('/' . $date . '/i', $recruiterOffTimes);

        foreach ($dataHours as $dataHour) {
            if ($hours == explode(' ', $dataHour)[1]) {
                throw new InputException(trans('validation.ERR.036'));
            }
        }

        return $this->userUpdateApplication($application, $data);
    }

    /**
     * @param $application
     * @param $data
     * @return bool
     * @throws InputException
     */
    public function userUpdateApplication($application, $data)
    {
        $user = $this->user;
        $nameUser = $user->first_name . $user->last_name;

        try {
            DB::beginTransaction();

            $application->update($data);
            Notification::query()->create([
                'user_id' => @$application->store->owner->id,
                'notice_type_id' => Notification::TYPE_UPDATE_INTERVIEW_APPLY,
                'noti_object_ids' => [
                    'store_id' => $application->store_id,
                    'application_id' => $application->id,
                ],
                'title' => trans('notification.N015.title', ['user_name' => $nameUser]),
                'content' => trans('notification.N015.content', ['user_name' => $nameUser]),
            ]);

            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception);
        }
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
                return true;
            }
        }

        return false;
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
            'interview_approach_id' => $interviewApproaches->id,
            'date' => $data['date'],
            'hours' => $data['hours'],
            'note' => $data['note'],
        ];
    }
}
