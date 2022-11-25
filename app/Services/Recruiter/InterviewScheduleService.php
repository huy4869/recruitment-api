<?php

namespace App\Services\Recruiter;

use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\RecruiterOffTime;
use App\Models\Store;
use App\Services\Service;
use App\Services\User\Job\JobService;
use App\Services\User\JobPostingService;
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
     * @param $date
     * @return array
     */
    public function getInterviewSchedule($date)
    {
        $date = DateTimeHelper::firstDayOfWeek($date);

        if ($date) {
            $recruiterOffTimes = $this->getRecruiterOffTimes($date);
            $applications = $this->getApplicationOffTimes($date);

            return $this->resultDate($date, $recruiterOffTimes, $applications);
        }

        return [];
    }

    /**
     * @param $date
     * @return array
     */
    public function getApplicationOffTimes($date)
    {
        $data = [];
        $startDate = now()->format(config('date.format_date'));
        $endDate = Carbon::parse($date)->addDays(config('date.day_of_week'))->format(config('date.format_date'));
        $stores = Store::query()
            ->with(['applications' => function ($query) use ($startDate, $endDate) {
                $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate)
                    ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW]);
            }, 'applications.applicationUser'])
            ->where('user_id', '=', $this->user->id)->get();
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

            $data[$applicationDate][$application->hours] = $nameUserApplication;
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
     * @return mixed
     */
    public function getRecruiterOffTimes($date)
    {
        $startMonthOfWeek = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $endMonthOfWeek = Carbon::parse($date)->addDays(config('date.day_of_week'))->firstOfMonth()->format('Y-m-d');
        $recruiterOffTimes = RecruiterOffTime::query()->where('user_id', '=', $this->user->id)->first()->off_times ?? [];

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

            if (isset($recruiterOffTimes[$date . ' ' . $time])) {
                $isNotGood = !$isNotGood;
                $isGood = InterviewScheduleService::RESULT;
            }

            if (isset($applications[$time])) {
                $applierName = $applications[$time];
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
            }

            $data[] = [
                'hours' => $time,
                'is_past' => $isPast,
                'is_good' => $isGood,
                'is_not_good' => $isNotGood,
                'is_has_interview' => $isHasInterview,
                'applier_name' => $applierName,
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
            ];
        }

        return $data;
    }

    /**
     * Update or create or delete recruiter off time
     *
     * @param $data
     * @return bool|Builder|Model|int
     * @throws ValidationException
     */
    public function updateOrCreateInterviewSchedule($data)
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
                    ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW]);
            }, 'applications.user'])
            ->where('user_id', '=', $this->user->id)->get();

        foreach ($stores as $store) {
            if ($store->applications->count()) {
                throw ValidationException::withMessages([
                    'hours' => trans('validation.ERR.037')
                ]);
            }
        }

        $dateTime = $date . ' ' . $hours;
        $firstMonth = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $recruiterOffTime = RecruiterOffTime::query()->where('user_id', '=', $this->user->id)->first();

        if (!$recruiterOffTime) {
            return RecruiterOffTime::query()->create([
                'user_id' => $this->user->id,
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
     * update
     *
     * @param $date
     * @return mixed
     */
    public function updateOrCreateInterviewScheduleDate($date)
    {
        $storeIds = $this->user->stores->pluck('id')->toArray();
        $firstMonth = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');

        $hours = Application::query()
            ->whereIn('store_id', $storeIds)
            ->whereDate('date', $date . ' 00:00:00')
            ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW])
            ->get()->pluck('hours')->toArray();

        $recruiterOffTimes = $this->user->recruiterOffTimes->off_times;
        $defaultHours = config('date.time');

        if ($date == now()->format('Y-m-d')) {
            $currentHour = DateTimeHelper::getTime();

            foreach ($defaultHours as $key => $hour) {
                if ($currentHour > $hour) {
                    unset($defaultHours[$key]);
                }
            }
        }

        $defaultHours = array_diff($defaultHours, $hours);

        if (isset($recruiterOffTimes[$firstMonth])) {
            $dateHoursRecs = preg_grep('/' . $date . '/i', $recruiterOffTimes[$firstMonth]);

            if (!$dateHoursRecs) {
                foreach ($defaultHours as $hour) {
                    $dateTime = $date . ' ' . $hour;
                    $recruiterOffTimes[$firstMonth][$dateTime] = $dateTime;
                }
            } else {
                $hoursRecOffTimes = [];
                foreach ($dateHoursRecs as $dateHour) {
                    $hoursRecOffTimes[] = explode(' ', $dateHour)[1];
                }

                $defaultHours = array_diff($defaultHours, $hoursRecOffTimes);

                if ($defaultHours) {
                    foreach ($defaultHours as $hour) {
                        $dateTime = $date . ' ' . $hour;
                        $recruiterOffTimes[$firstMonth][$dateTime] = $dateTime;
                    }
                } else {
                    foreach ($dateHoursRecs as $dateHoursRec) {
                        unset($recruiterOffTimes[$firstMonth][$dateHoursRec]);
                    }
                }
            }
        } else {
            $dateTimes = [];

            foreach ($defaultHours as $hour) {
                $dateTime = $date . ' ' . $hour;
                $dateTimes[$dateTime] = $dateTime;
            }

            $recruiterOffTimes = array_merge([
                $firstMonth => $dateTimes
            ], $recruiterOffTimes);
        }

        return $this->user->recruiterOffTimes->update(['off_times' => $recruiterOffTimes]);
    }
}
