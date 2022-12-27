<?php

namespace App\Services\Recruiter;

use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\Store;
use App\Models\StoreOffTime;
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
     * @param $date
     * @param $storeIds
     * @return array
     */
    public function getInterviewSchedule($date, $storeIds)
    {
        $date = DateTimeHelper::firstDayOfWeek($date);

        if ($date) {
            $storeOffTimes = [];

            if ($storeIds && count($storeIds) == 1) {
                $storeOffTimes = $this->getStoreOffTimes($date, $storeIds);
            }

            $applications = $this->getApplicationOffTimes($date, $storeIds);

            return $this->resultDate($date, $storeOffTimes, $applications);
        }

        return [];
    }

    /**
     * @param $date
     * @param array $storeIds
     * @return array
     */
    public function getApplicationOffTimes($date, $storeIds = [])
    {
        $data = [];
        $startDate = now()->format(config('date.format_date'));
        $endDate = Carbon::parse($date)->addDays(config('date.day_of_week'))->format(config('date.format_date'));
        $applications = Application::query()
            ->with('applicationUser')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where('interview_status_id', '=', MInterviewStatus::STATUS_WAITING_INTERVIEW);

        if (!$storeIds) {
            $storeIds = $this->user->stores->pluck('id')->toArray();
        }

        $applications->whereIn('store_id', $storeIds);

        foreach ($applications->get() as $application) {
            $applicationDate = explode(' ', $application->date)[0];

            if (@$application->applicationUser->first_name && @$application->applicationUser->last_name) {
                $nameUserApplication = @$application->applicationUser->first_name . ' ' . @$application->applicationUser->last_name;
            } else {
                $nameUserApplication = @$application->applicationUser->email;
            }

            $data[$applicationDate][$application->hours][] = [
                $nameUserApplication,
                @$application->user_id,
                $application->id,
                $application->store->hex_color
            ];
        }

        return $data;
    }

    /**
     * @param $date
     * @param array $storeOffTimes
     * @param array $applications
     * @return array
     */
    public function resultDate($date, $storeOffTimes = [], $applications = [])
    {
        $data = [];
        $i = 0;

        while ($i <= config('date.day_of_week')) {
            $dateCheck = Carbon::parse($date)->addDays($i)->format('Y-m-d');

            if ($dateCheck < now()->format('Y-m-d')) {
                $times = $this->timePast();
            } else {
                $dataHours = preg_grep('/' . $dateCheck . '/i', $storeOffTimes);
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
     * @param $storeIds
     * @return mixed
     */
    public function getStoreOffTimes($date, $storeIds)
    {
        $startMonthOfWeek = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $endMonthOfWeek = Carbon::parse($date)->addDays(config('date.day_of_week'))->firstOfMonth()->format('Y-m-d');
        $storeOffTimes = StoreOffTime::query()->where('store_id', '=', $storeIds[0])->first();
        $storeOffTimes = $storeOffTimes ? $storeOffTimes->off_times : [];

        return JobService::resultStoreOffTimes([$startMonthOfWeek, $endMonthOfWeek], $storeOffTimes);
    }

    /**
     * Time check
     *
     * @param $date
     * @param array $storeOffTimes
     * @param array $applications
     * @return array
     */
    public function checkTime($date, $storeOffTimes = [], $applications = [])
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
            $applicationUsers = [];

            if (isset($storeOffTimes[$date . ' ' . $time])) {
                $isNotGood = !$isNotGood;
                $isGood = InterviewScheduleService::RESULT;
            }

            if (isset($applications[$time])) {
                foreach ($applications[$time] as $userApply) {
                    $applicationUsers[] = [
                        'hex_color' => $userApply[3] ?? null,
                        'id' => $userApply[2] ?? null,
                        'user_id' => $userApply[1] ?? null,
                        'name' => $userApply[0] ?? null,
                    ];
                }
                $isHasInterview = !$isHasInterview;
                $isNotGood = InterviewScheduleService::RESULT;
                $isGood = InterviewScheduleService::RESULT;
            }

            if ($date == now()->format('Y-m-d') && ($key < $checkTime || time() > $endTime)) {
                $isPast = !InterviewScheduleService::RESULT;
                $isGood = InterviewScheduleService::RESULT;
                $isNotGood = InterviewScheduleService::RESULT;
                $isHasInterview = InterviewScheduleService::RESULT;
                $applicationUsers = [];
            }

            $data[] = [
                'hours' => $time,
                'is_past' => $isPast,
                'is_good' => $isGood,
                'is_not_good' => $isNotGood,
                'is_has_interview' => $isHasInterview,
                'application_users' => $applicationUsers
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
                'application_users' => []
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
                        'hours' => trans('validation.ERR.038')
                    ]);
                }
            }
        }

        $applications = Application::query()
            ->where('store_id', '=', $data['store_id'])
            ->whereDate('date', '=', $date)
            ->where('hours', '=', $hours)
            ->where('interview_status_id', '=', MInterviewStatus::STATUS_WAITING_INTERVIEW)
            ->exists();

        if ($applications) {
            throw ValidationException::withMessages([
                'hours' => trans('validation.ERR.038')
            ]);
        }

        $dateTime = $date . ' ' . $hours;
        $firstMonth = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $storeOffTime = StoreOffTime::query()->where('store_id', '=', $data['store_id'])->first();

        if (!$storeOffTime) {
            return StoreOffTime::query()->create([
                'store_id' => $data['store_id'],
                'off_times' => [
                    $firstMonth => [
                        $dateTime => $dateTime
                    ]
                ]
            ]);
        }

        $dataOffTimes = $storeOffTime['off_times'];

        if ($data['is_has_interview'] == InterviewScheduleService::IS_HAS_INTERVIEW) {
            if (isset($dataOffTimes[$firstMonth])) {
                $dataOffTimes[$firstMonth][$dateTime] = $dateTime;
            } else {
                $dateTimes = [$dateTime => $dateTime];
                $dataOffTimes = array_merge([
                    $firstMonth => $dateTimes
                ], $dataOffTimes);
            }

            return $storeOffTime->update(['off_times' => $dataOffTimes]);
        }

        unset($dataOffTimes[$firstMonth][$dateTime]);

        return $storeOffTime->update(['off_times' => $dataOffTimes]);
    }

    /**
     * update date interview schedule
     *
     * @param $date
     * @param $storeId
     * @return mixed
     */
    public function updateOrCreateInterviewScheduleDate($date, $storeId)
    {
        $firstMonth = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $hours = Application::query()
            ->where('store_id', '=', $storeId)
            ->whereDate('date', $date . ' 00:00:00')
            ->where('interview_status_id', '=', MInterviewStatus::STATUS_WAITING_INTERVIEW)
            ->get()->pluck('hours')->toArray();

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
        $storeOffTimes = StoreOffTime::query()->where('store_id', '=', $storeId)->first();

        if (!$storeOffTimes) {
            $dateTimes = [];

            foreach ($defaultHours as $hour) {
                $dateTime = $date . ' ' . $hour;
                $dateTimes[$firstMonth][$dateTime] = $dateTime;
            }

            return StoreOffTime::query()->create([
                'store_id' => $storeId,
                'off_times' => $dateTimes
            ]);
        }

        $resultStoreOffTimes = $storeOffTimes->off_times;

        if (isset($resultStoreOffTimes[$firstMonth])) {
            $dateHoursStores = preg_grep('/' . $date . '/i', $resultStoreOffTimes[$firstMonth]);

            if (!$dateHoursStores) {
                foreach ($defaultHours as $hour) {
                    $dateTime = $date . ' ' . $hour;
                    $resultStoreOffTimes[$firstMonth][$dateTime] = $dateTime;
                }
            } else {
                $hoursStoreOffTimes = [];
                foreach ($dateHoursStores as $dateHour) {
                    $hoursStoreOffTimes[] = explode(' ', $dateHour)[1];
                }

                $defaultHours = array_diff($defaultHours, $hoursStoreOffTimes);

                if ($defaultHours) {
                    foreach ($defaultHours as $hour) {
                        $dateTime = $date . ' ' . $hour;
                        $resultStoreOffTimes[$firstMonth][$dateTime] = $dateTime;
                    }
                } else {
                    foreach ($dateHoursStores as $dateHoursStore) {
                        unset($resultStoreOffTimes[$firstMonth][$dateHoursStore]);
                    }
                }
            }
        } else {
            $dateTimes = [];

            foreach ($defaultHours as $hour) {
                $dateTime = $date . ' ' . $hour;
                $dateTimes[$dateTime] = $dateTime;
            }

            $resultStoreOffTimes = array_merge([
                $firstMonth => $dateTimes
            ], $resultStoreOffTimes);
        }

        return $storeOffTimes->update(['off_times' => $resultStoreOffTimes]);
    }
}
