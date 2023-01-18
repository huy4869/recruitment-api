<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\MInterviewApproach;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use App\Models\StoreOffTime;
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
    public const STATUS_INTERVIEW_RECRUIT_END = 7;

    /**
     * List user applications
     *
     * @return Builder[]|Collection
     */
    public function list()
    {
        $applications = Application::query()
            ->with([
                'jobPostingAcceptTrashed',
                'storeAcceptTrashed',
                'storeAcceptTrashed.owner' => function ($q) {
                    $q->withTrashed();
                },
                'interviews',
                'jobPostingAcceptTrashed.bannerImageAcceptTrashed'
            ])
            ->where('user_id', $this->user->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return $applications;
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
                $query->whereIn('id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW]);
            })
            ->whereHas('jobPosting', function ($query) {
                $query->whereNotIn('job_status_id', [JobPosting::STATUS_DRAFT, JobPosting::STATUS_HIDE]);
            })
            ->where(DB::raw("CONCAT(DATE_FORMAT(date,'%Y/%m/%d'),' ',hours)"), '>=', DateTimeHelper::formatDateTime(now()))
            ->with([
                'store',
                'store.owner',
                'jobPosting',
                'jobPosting.province',
                'jobPosting.provinceCity',
                'interviewApproach'
            ])
            ->orderBy('date', 'asc')
            ->orderBy('hours', 'asc')
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
     * @throws Exception
     */
    public function cancelApplied($id)
    {
        $statusCanCancel = [
            MInterviewStatus::STATUS_APPLYING,
            MInterviewStatus::STATUS_WAITING_INTERVIEW,
            MInterviewStatus::STATUS_WAITING_RESULT,
        ];

        $application = Application::query()
            ->with(['store.owner', 'user'])
            ->where('user_id', $this->user->id)
            ->where('id', $id)
            ->whereIn('interview_status_id', $statusCanCancel)
            ->first();

        if (!$application) {
            throw new InputException(trans('response.invalid'));
        }

        try {
            DB::beginTransaction();

            $application->update(['interview_status_id' => MInterviewStatus::STATUS_CANCELED]);
            Notification::query()->create([
                'user_id' => $application->store->owner->id,
                'notice_type_id' => Notification::TYPE_CANCEL_APPLY,
                'noti_object_ids' => [
                    'store_id' => $application->store_id,
                    'job_id' => $application->job_posting_id,
                    'application_id' => $application->id,
                ],
                'title' => __('notification.NOO4.title', ['user_name' => $application->user->first_name . $application->user->last_name]),
                'content' => __('notification.NOO4.content'),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new Exception($exception->getMessage());
        }
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

        $today = now()->format(config('date.fe_date_format'));
        $tomorrow = now()->addDays()->format(config('date.fe_date_format'));
        $dayAfterTomorrow = now()->addDays(2)->format(config('date.fe_date_format'));

        foreach ($userInterviews as $interview) {
            $interviewStatus = $interview->interview_status_id;
            $interviewDate = Carbon::parse($interview->date)->format(config('date.fe_date_format'));
            $interview->can_cancel = !in_array($interviewStatus, [MInterviewStatus::STATUS_ACCEPTED, MInterviewStatus::STATUS_CANCELED, MInterviewStatus::STATUS_REJECTED]) && $interview->jobPostingAcceptTrashed->job_status_id == JobPosting::STATUS_RELEASE;
            $interview->can_change_interview = $interviewStatus == MInterviewStatus::STATUS_APPLYING && $interview->jobPostingAcceptTrashed->job_status_id == JobPosting::STATUS_RELEASE;

            if ($today == $interviewDate) {
                $interview->date_status = trans('common.today');
                continue;
            }

            if ($tomorrow == $interviewDate) {
                $interview->date_status = trans('common.tomorrow');
                continue;
            }

            if ($dayAfterTomorrow == $interviewDate) {
                $interview->date_status = trans('common.day_after_tomorrow');
            }
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
        $application = Application::query()->with('jobPosting', function ($q) {
            $q->withTrashed();
        })
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $applicationId)
            ->first();

        if (!$application || !is_null($application->jobPosting->deleted_at) || $application->jobPosting->job_status_id === JobPosting::STATUS_DRAFT) {
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

        $data = $this->saveMakeData($data);

        if ($date == $dateApplication && $hours == $hoursApplication) {
            return $this->userUpdateApplication($application, $data);
        }

        if (($date == $now && $this->checkTimeUpdate($hours))
            || ($date == $dateApplication && $date < $now && $hours != $hoursApplication)
            || ($date != $dateApplication && $date < $now)) {
            throw ValidationException::withMessages([
                'date' => trans('validation.ERR.037')
            ]);
        }

        $month = Carbon::parse($data['date'])->firstOfMonth()->format('Y-m-d');
        $storeOffTimes = StoreOffTime::query()->where('store_id', '=', $application->store_id)->first();

        if ($storeOffTimes && isset($storeOffTimes->off_times[$month])) {
            $dataHours = preg_grep('/' . $date . '/i', $storeOffTimes->off_times[$month]);

            if (isset($dataHours[$date . ' ' . $hours])) {
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
