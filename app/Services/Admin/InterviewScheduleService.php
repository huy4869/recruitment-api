<?php

namespace App\Services\Admin;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\MInterviewApproach;
use App\Models\MInterviewStatus;
use App\Models\RecruiterOffTime;
use App\Models\Store;
use App\Models\User;
use App\Services\Service;
use App\Services\User\Job\JobService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class InterviewScheduleService extends Service
{
    public const RESULT = false;
    public const NO_HAS_INTERVIEW = 0;
    public const IS_HAS_INTERVIEW = 1;

    /**
     * @param $data
     * @return array
     */
    public function getInterviewSchedule($data)
    {
        $date = DateTimeHelper::firstDayOfWeek($data['start_date']);

        if ($date) {
            $recruiterOffTimes = $this->getRecruiterOffTimes($date, $data['rec_id']);
            $applications = $this->getApplicationOffTimes($date, $data['rec_id']);

            return $this->resultDate($date, $recruiterOffTimes, $applications);
        }

        return [];
    }

    /**
     * @param $date
     * @param $recId
     * @return array
     */
    public function getApplicationOffTimes($date, $recId)
    {
        $data = [];
        $startDate = now()->format(config('date.format_date'));
        $endDate = Carbon::parse($date)->addDays(config('date.day_of_week'))->format(config('date.format_date'));
        $stores = Store::query()
            ->with(['applications' => function ($query) use ($startDate, $endDate) {
                $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->where('interview_status_id', MInterviewStatus::STATUS_WAITING_INTERVIEW);
            }, 'applications.applicationUser'])
            ->where('user_id', '=', $recId)->get();
        $applications = collect();

        foreach ($stores as $store) {
            $applications = $applications->merge($store->applications);
        }

        foreach ($applications as $application) {
            $applicationDate = explode(' ', $application->date)[0];

            if (@$application->applicationUser->first_name && @$application->applicationUser->last_name) {
                $nameUserApplication = @$application->applicationUser->first_name . ' ' . @$application->applicationUser->last_name;
            } else {
                $nameUserApplication = @$application->applicationUser->email;
            }

            $data[$applicationDate][$application->hours] = [
                'name' => $nameUserApplication,
                'application_id' => $application->id,
                'user_id' => $application->user_id,
            ];
        }

        return $data;
    }

    /**
     * @param $date
     * @param array $recruiterOffTimes
     * @param array $applications
     * @return array
     */
    public function resultDate($date, $recruiterOffTimes = [], $applications = [])
    {
        $data = [];
        $i = 0;

        while ($i <= config('date.day_of_week')) {
            $dateCheck = Carbon::parse($date)->addDays($i)->format('Y-m-d');

            if ($dateCheck < now()->format('Y-m-d')) {
                $times = $this->timePast();
            } else {
                $dataHours = preg_grep('/' . $dateCheck . '/i', $recruiterOffTimes);
                $applicationTimes = isset($applications[$dateCheck]) ? $applications[$dateCheck] : [];
                $times = $this->checkTime($dateCheck, $dataHours, $applicationTimes);
            }

            $data[] = [
                'date' => $dateCheck,
                'date_format' => DateTimeHelper::formatDayOfMothFe($dateCheck),
                'times' => $times,
            ];

            ++$i;
        }//end while

        return $data;
    }

    /**
     * @param $date
     * @param $recId
     * @return mixed
     */
    public function getRecruiterOffTimes($date, $recId)
    {
        $startMonthOfWeek = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $endMonthOfWeek = Carbon::parse($date)->addDays(config('date.day_of_week'))->firstOfMonth()->format('Y-m-d');
        $user = User::query()
            ->with('recruiterOffTimes')
            ->where('id', '=', $recId)
            ->first();

        $recruiterOffTimes = @$user->recruiterOffTimes->off_times ?? [];

        return JobService::resultRecruiterOffTimes([$startMonthOfWeek, $endMonthOfWeek], $recruiterOffTimes);
    }

    /**
     * Time check
     *
     * @param $date
     * @param array $recruiterOffTimes
     * @param array $applications
     * @return array
     */
    public function checkTime($date, $recruiterOffTimes = [], $applications = [])
    {
        $data = [];
        $currentHour = DateTimeHelper::getTime();
        $endTime = strtotime(date('Y-m-d' . config('date.time_max')));
        $checkTime = array_search($currentHour, config('date.time'));

        foreach (config('date.time') as $key => $time) {
            $isPast = InterviewScheduleService::RESULT;
            $isGood = !InterviewScheduleService::RESULT;
            $isNotGood = InterviewScheduleService::RESULT;
            $isHasInterview = InterviewScheduleService::RESULT;
            $applierName = '';
            $applierId = null;
            $applierUserId = null;

            if (isset($recruiterOffTimes[$date . ' ' . $time])) {
                $isNotGood = !$isNotGood;
                $isGood = InterviewScheduleService::RESULT;
            }

            if (isset($applications[$time])) {
                $applierName = $applications[$time]['name'];
                $applierId = $applications[$time]['application_id'];
                $applierUserId = $applications[$time]['user_id'];
                $isHasInterview = !$isHasInterview;
                $isNotGood = InterviewScheduleService::RESULT;
                $isGood = InterviewScheduleService::RESULT;
            }

            if ($date == now()->format('Y-m-d') && ($key < $checkTime || time() > $endTime)) {
                $isPast = !InterviewScheduleService::RESULT;
                $isGood = InterviewScheduleService::RESULT;
                $isNotGood = InterviewScheduleService::RESULT;
                $isHasInterview = InterviewScheduleService::RESULT;
                $applierName = '';
                $applierId = null;
                $applierUserId = null;
            }

            $data[] = [
                'hours' => $time,
                'is_past' => $isPast,
                'is_good' => $isGood,
                'is_not_good' => $isNotGood,
                'is_has_interview' => $isHasInterview,
                'applier_name' => $applierName,
                'applier_id' => $applierId,
                'applier_user_id' => $applierUserId,
            ];
        }//end foreach

        return $data;
    }

    /**
     * @return array
     */
    public function timePast()
    {
        $data = [];

        foreach (config('date.time') as $time) {
            $data[] = [
                'hours' => $time,
                'is_past' => !InterviewScheduleService::RESULT,
                'is_good' => InterviewScheduleService::RESULT,
                'is_not_good' => InterviewScheduleService::RESULT,
                'is_has_interview' => InterviewScheduleService::RESULT,
                'applier_name' => '',
                'applier_id' => '',
                'applier_user_id' => '',
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function getRecIds()
    {
        return User::query()->roleRecruiter()->get()->pluck('id')->toArray();
    }

    /**
     * Admin update application
     *
     * @param $applicationId
     * @param $data
     * @return int
     * @throws InputException
     * @throws ValidationException
     */
    public function updateApplication($applicationId, $data)
    {
        if (!in_array($data['hours'], config('date.time'))) {
            throw new InputException(trans('validation.ERR.999'));
        }

        $application = Application::query()
            ->with(['store.owner.recruiterOffTimes', 'store.owner.stores.applications'])
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

        if ($this->checkTimeUpdate($now, $date, $hours, $dateApplication, $hoursApplication)) {
            throw ValidationException::withMessages([
                'date' => trans('validation.ERR.037')
            ]);
        }

        $data = $this->saveMakeData($data);

        if ($date == $dateApplication && $hours == $hoursApplication) {
            return $application->update($data);
        }

        $applications = Application::query()
            ->whereIn('interview_status_id', MInterviewStatus::STATUS_WAITING_INTERVIEW)
            ->where('id', '!=', $applicationId)
            ->whereDate('date', $date)
            ->where('hours', '=', $hours)
            ->where(function ($query) use ($application) {
                $query->where('user_id', '=', $application->user_id)
                    ->orWhere('job_posting_id', '=', $application->job_posting_id);
            })
            ->exists();

        if ($applications) {
            throw new InputException(trans('validation.ERR.036'));
        }

        $storeIds = $application->store->owner->stores->pluck('id')->toArray();
        $userApplications = Application::query()
            ->whereIn('store_id', $storeIds)
            ->where('date', '=', $date . ' 00:00:00')
            ->where('hours', '=', $hours)
            ->where('id', '!=', $application->id)
            ->where('interview_status_id', MInterviewStatus::STATUS_WAITING_INTERVIEW)
            ->exists();

        if ($userApplications) {
            throw new InputException(trans('validation.ERR.036'));
        }

        $month = Carbon::parse($data['date'])->firstOfMonth()->format('Y-m-d');
        $recruiterOffTimes = $application->store->owner->recruiterOffTimes->off_times ?? [];
        $recruiterOffTimes = $recruiterOffTimes[$month];
        $dataHours = preg_grep('/' . $date . '/i', $recruiterOffTimes);

        if (isset($dataHours[$date . ' ' . $hours])) {
            throw new InputException(trans('validation.ERR.036'));
        }

        return $application->update($data);
    }

    /**
     * @param $now
     * @param $date
     * @param $hours
     * @param $dateApplication
     * @param $hoursApplication
     * @return false
     */
    public function checkTimeUpdate($now, $date, $hours, $dateApplication, $hoursApplication)
    {
        $timeNow = DateTimeHelper::getTime();
        $checkTime = in_array($timeNow, config('date.time'));

        if (($date == $now && (!$checkTime || $hours <= $timeNow))
            || ($date < $now && !($date == $dateApplication && $hours == $hoursApplication))
        ) {
            return true;
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
            'update_times' => now(),
        ];
    }

    /**
     * Admin update or create or delete recruiter off time
     *
     * @param $recId
     * @param $data
     * @return bool|Builder|Model|int
     * @throws ValidationException
     */
    public function updateOrCreateInterviewSchedule($recId, $data)
    {
        $date = $data['date'];
        $hours = $data['hours'];
        $currentHour = DateTimeHelper::getTime();
        $endTime = strtotime(date('Y-m-d' . config('date.time_max')));
        $checkTime = array_search($currentHour, config('date.time'));

        if ($date == now()->format('Y-m-d')) {
            foreach (config('date.time') as $key => $time) {
                if (($key < $checkTime || time() > $endTime) && $hours == $time) {
                    throw ValidationException::withMessages([
                        'hours' => trans('validation.ERR.037')
                    ]);
                }
            }
        }

        $stores = Store::query()
            ->with(['applications' => function ($query) use ($date, $hours) {
                $query->whereDate('date', $date)
                    ->where('hours', '=', $hours)
                    ->whereIn('interview_status_id', MInterviewStatus::STATUS_WAITING_INTERVIEW);
            }, 'applications.user'])
            ->where('user_id', '=', $recId)->get();

        foreach ($stores as $store) {
            if ($store->applications->count()) {
                throw ValidationException::withMessages([
                    'hours' => trans('validation.ERR.037')
                ]);
            }
        }

        $dateTime = $date . ' ' . $hours;
        $firstMonth = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $recruiterOffTime = RecruiterOffTime::query()->where('user_id', '=', $recId)->first();

        if (!$recruiterOffTime) {
            return RecruiterOffTime::query()->create([
                'user_id' => $recId,
                'off_times' => [
                    $firstMonth => [
                        $dateTime => $dateTime
                    ]
                ]
            ]);
        }

        $dataOffTimes = $recruiterOffTime['off_times'];

        if ($data['is_has_interview'] == InterviewScheduleService::IS_HAS_INTERVIEW) {
            if (isset($dataOffTimes[$firstMonth])) {
                $dataOffTimes[$firstMonth][$dateTime] = $dateTime;
            } else {
                $dateTimes = [$dateTime => $dateTime];
                $dataOffTimes = array_merge([
                    $firstMonth => $dateTimes
                ], $dataOffTimes);
            }

            return $recruiterOffTime->update(['off_times' => $dataOffTimes]);
        }

        unset($dataOffTimes[$firstMonth][$dateTime]);

        return $recruiterOffTime->update(['off_times' => $dataOffTimes]);
    }

    /**
     * @param $applicationId
     * @return array
     * @throws InputException
     */
    public function detailUserApplication($applicationId)
    {
        $application = Application::query()->where('id', '=', $applicationId)->first();

        if (!$application) {
            throw new InputException(trans('response.not_found'));
        }

        return $this->detailApplicationAndTimes($application, $this->detailJobUserApplication($application->job_posting_id, $application));
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
                'interview_approach_id' => $application->interview_approach_id,
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
     * @param $dataApplications
     * @return array
     */
    public static function resultApplication($dataApplications)
    {
        $data = [];
        foreach ($dataApplications as $application) {
            $data[explode(' ', $application->date)[0]][] = $application->hours;
        }

        return $data;
    }

    /**
     * Recruiter off times
     *
     * @param $recruiterOffTimes
     * @param $key
     * @return mixed
     */
    public static function resultRecruiterOffTimes($key, $recruiterOffTimes = [])
    {
        $keys = array_map('strval', $key);

        return call_user_func_array('array_merge', array_values(array_intersect_key($recruiterOffTimes, array_flip($keys))));
    }

    /**
     * Check Time Date
     *
     * @param $id
     * @param $application
     * @return array
     * @throws InputException
     */
    public function detailJobUserApplication($id, $application)
    {
        $jobPosting = $this->checkJobPosting($id, $application->user_id);
        $storeIds = $jobPosting->store->owner->stores->pluck('id')->toArray();
        $now = now()->format('Y-m-d 00:00:00');
        $userId = $application->user_id;

        $recruiterInterviewApplications = Application::query()
            ->whereIn('store_id', $storeIds)
            ->where('job_posting_id', '!=', $jobPosting->id)
            ->where('date', '>=', $now)
            ->get();
        $applicationsTime = $jobPosting->applications;
        $userApplicationsTime = Application::query()
            ->where('user_id', $userId)
            ->where('job_posting_id', '!=', $jobPosting->id)
            ->whereDate('date', '>=', $now)
            ->get();
        $recruiterOffTimes = $jobPosting->store->owner->recruiterOffTimes->off_times ?? [];
        $monthNow = now()->firstOfMonth()->format('Y-m-d');
        $monthDay = now()->addDays(config('date.max_day'))->firstOfMonth()->format('Y-m-d');

        return $this->resultDateApplication(
            self::resultApplication($applicationsTime),
            self::resultApplication($userApplicationsTime),
            self::resultApplication($recruiterInterviewApplications),
            self::resultRecruiterOffTimes([$monthNow, $monthDay], $recruiterOffTimes),
            $application
        );
    }

    /**
     * @param $applicationsTime
     * @param $userApplicationsTime
     * @param $recruiterApplicationOther
     * @param $recruiterOffTimes
     * @param null $application
     * @return mixed
     */
    public function resultDateApplication($applicationsTime, $userApplicationsTime, $recruiterApplicationOther, $recruiterOffTimes, $application = null)
    {
        $dateStart = [];
        $i = 0;

        while ($i <= config('date.max_date')) {
            $dateCheck = now()->addDays($i)->format('Y-m-d');
            $applicationsTimes = $applicationsTime[$dateCheck] ?? [];
            $userApplicationsTimes = $userApplicationsTime[$dateCheck] ?? [];
            $recruiterApplicationOthers = $recruiterApplicationOther[$dateCheck] ?? [];
            $timeChecks = array_merge($applicationsTimes, $userApplicationsTimes, $recruiterApplicationOthers);
            $dataHours = preg_grep('/' . $dateCheck . '/i', $recruiterOffTimes);

            foreach ($dataHours as $dataHour) {
                $timeChecks[] = explode(' ', $dataHour)[1];
            }

            if ($application && $application->date == now()->format('Y-m-d 00:00:00')) {
                $times = $this->checkTimeApplication($dateCheck, $timeChecks, $application->hours);
            } else {
                $times = $this->checkTimeApplication($dateCheck, $timeChecks);
            }

            $dateStart[] = [
                'date' => $dateCheck,
                'date_format' => DateTimeHelper::formatDateDayOfWeekJa($dateCheck),
                'is_enable' => $times['is_enabled_date'],
                'times' => $times['times']
            ];

            $i++;
        }//end while

        return $dateStart;
    }

    /**
     * Time check
     *
     * @param $date
     * @param array $timeCoincides
     * @param null $hours
     * @return array
     */
    public function checkTimeApplication($date, $timeCoincides = [], $hours = null)
    {
        $data = [];
        $isEnabledDate = false;
        $currentHour = DateTimeHelper::getTime();
        $endTime = strtotime(date('Y-m-d' . config('date.time_max')));
        $checkTime = array_search($currentHour, config('date.time'));
        $checkTime = $checkTime ? $checkTime : 0;

        foreach (config('date.time') as $key => $time) {
            if (in_array($time, $timeCoincides) ||
                ($date == now()->format('Y-m-d') && ($key < $checkTime || time() > $endTime))
                && $hours != $time
            ) {
                $data[] = [
                    'hours' => $time,
                    'is_enabled_time' => 0
                ];
            } else {
                $data[] = [
                    'hours' => $time,
                    'is_enabled_time' => 1
                ];
                $isEnabledDate = true;
            }
        }

        return [
            'is_enabled_date' => $isEnabledDate,
            'times' => $data,
        ];
    }

    /**
     * Check job posting
     *
     * @param $jobPostingId
     * @return Builder|Model|object
     * @throws InputException
     */
    public function checkJobPosting($jobPostingId, $userId)
    {
        $jobPosting = JobPosting::query()
            ->released()
            ->with(['applications' => function($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            }, 'store.owner.recruiterOffTimes', 'store.owner.stores.applications'])
            ->where('id', '=', $jobPostingId)
            ->first();

        if (!$jobPosting) {
            throw new InputException(trans('response.not_found'));
        }

        return $jobPosting;
    }
}
