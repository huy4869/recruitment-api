<?php

namespace App\Services\Admin;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\MInterviewApproach;
use App\Models\MInterviewStatus;
use App\Models\Store;
use App\Models\StoreOffTime;
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
            $storeId = $data['store_id'];
            $storeOffTimes = $this->getStoreOffTimes($date, $storeId);
            $applications = $this->getApplicationOffTimes($date, $storeId);

            return $this->resultDate($date, $storeOffTimes, $applications);
        }

        return [];
    }

    /**
     * @param $date
     * @param $storeId
     * @return array
     */
    public function getApplicationOffTimes($date, $storeId)
    {
        $data = [];
        $startDate = now()->format(config('date.format_date'));
        $endDate = Carbon::parse($date)->addDays(config('date.day_of_week'))->format(config('date.format_date'));
        $applications = Application::query()
            ->where('store_id', $storeId)
            ->with([
                'applicationUser',
                'store'
            ])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where('interview_status_id', MInterviewStatus::STATUS_WAITING_INTERVIEW)
            ->get();

        foreach ($applications as $application) {
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
     * @param $storeId
     * @return mixed
     */
    public function getStoreOffTimes($date, $storeId)
    {
        $startMonthOfWeek = Carbon::parse($date)->firstOfMonth()->format('Y-m-d');
        $endMonthOfWeek = Carbon::parse($date)->addDays(config('date.day_of_week'))->firstOfMonth()->format('Y-m-d');

        $storeOffTimes = StoreOffTime::query()->where('store_id', $storeId)->first();
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

        $application = Application::query()->where('id', '=', $applicationId)->first();

        if (!$application) {
            throw new InputException(trans('response.not_found'));
        }

        $date = $data['date'];
        $hours = $data['hours'];
        $now = now()->format('Y-m-d');
        $dateApplication = explode(' ', $application->date)[0];
        $hoursApplication = $application->hours;

        $data = $this->saveMakeData($data);

        if ($date == $dateApplication && $hours == $hoursApplication) {
            return $application->update($data);
        }

        if ($this->checkTimeUpdate($now, $date, $hours, $dateApplication, $hoursApplication)) {
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

        $applications = Application::query()
            ->where('store_id', '=', $data['store_id'])
            ->whereDate('date', '=', $date)
            ->where('hours', '=', $hours)
            ->where('interview_status_id', MInterviewStatus::STATUS_WAITING_INTERVIEW)
            ->exists();

        if ($applications) {
            throw ValidationException::withMessages([
                'hours' => trans('validation.ERR.037')
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

        $dataOffTimes = $storeOffTime->off_times;

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

    /**
     * @param $applicationId
     * @return array
     * @throws InputException
     */
    public function detailUserApplication($applicationId)
    {
        $application = Application::query()
            ->where('id', '=', $applicationId)
            ->with(['jobPosting' => function ($q) {
                $q->withTrashed();
            }])
            ->withTrashed()
            ->first();

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

        $dataInterViewApproaches = MInterviewApproach::query()->get();
        $approach = [];

        foreach ($dataInterViewApproaches as $dataInterViewApproach) {
            $output = $dataInterViewApproach->name;

            if ($dataInterViewApproach->id == MInterviewApproach::STATUS_INTERVIEW_ONLINE) {
                $output .= sprintf('（%s）', config('application.interview_approach_online'));
            } elseif ($dataInterViewApproach->id == MInterviewApproach::STATUS_INTERVIEW_DIRECT) {
                $store = Store::query()->withTrashed()
                    ->where('id', $application->jobPosting->store_id)
                    ->with(['province', 'provinceCity'])
                    ->first();
                $output .= sprintf('（%s%s%s%s%s）',
                    $store->postal_code,
                    $store->province->name,
                    $store->provinceCity->name,
                    $store->address,
                    $store->building
                );
            }

            $approach[] = [
                'id' => $dataInterViewApproach->id,
                'name' => $output,
            ];
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
            'list_time' => array_merge($datePast, $times),
            'approach' => $approach,
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
     * Store off times
     *
     * @param $storeOffTimes
     * @param $key
     * @return mixed
     */
    public static function resultStoreOffTimes($key, $storeOffTimes = [])
    {
        $keys = array_map('strval', $key);

        return call_user_func_array('array_merge', array_values(array_intersect_key($storeOffTimes, array_flip($keys))));
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
        $now = now()->format('Y-m-d 00:00:00');
        $userId = $application->user_id;

        $recruiterInterviewApplications = Application::query()
            ->where('store_id', '=', $jobPosting->store_id)
            ->where('job_posting_id', '!=', $jobPosting->id)
            ->where('date', '>=', $now)
            ->get();
        $applicationsTime = $jobPosting->applications;
        $userApplicationsTime = Application::query()
            ->where('user_id', $userId)
            ->where('job_posting_id', '!=', $jobPosting->id)
            ->whereDate('date', '>=', $now)
            ->get();

        $storeOffTimes = StoreOffTime::query()->where('store_id', '=', $jobPosting->store_id)->first();
        $storeOffTimes = $storeOffTimes ? $storeOffTimes->off_times : [];
        $monthNow = now()->firstOfMonth()->format('Y-m-d');
        $monthDay = now()->addDays(config('date.max_day'))->firstOfMonth()->format('Y-m-d');

        return $this->resultDateApplication(
            self::resultApplication($applicationsTime),
            self::resultApplication($userApplicationsTime),
            self::resultApplication($recruiterInterviewApplications),
            self::resultStoreOffTimes([$monthNow, $monthDay], $storeOffTimes),
            $application
        );
    }

    /**
     * @param $applicationsTime
     * @param $userApplicationsTime
     * @param $recruiterApplicationOther
     * @param $storeOffTimes
     * @param null $application
     * @return mixed
     */
    public function resultDateApplication($applicationsTime, $userApplicationsTime, $recruiterApplicationOther, $storeOffTimes, $application = null)
    {
        $dateStart = [];
        $i = 0;

        while ($i <= config('date.max_date')) {
            $dateCheck = now()->addDays($i)->format('Y-m-d');
            $applicationsTimes = $applicationsTime[$dateCheck] ?? [];
            $userApplicationsTimes = $userApplicationsTime[$dateCheck] ?? [];
            $recruiterApplicationOthers = $recruiterApplicationOther[$dateCheck] ?? [];
            $timeChecks = array_merge($applicationsTimes, $userApplicationsTimes, $recruiterApplicationOthers);
            $dataHours = preg_grep('/' . $dateCheck . '/i', $storeOffTimes);

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
     * @param $userId
     * @return Builder|Model|object
     * @throws InputException
     */
    public function checkJobPosting($jobPostingId, $userId)
    {
        $jobPosting = JobPosting::query()
            ->withTrashed()
            ->with(['applications' => function($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            }])
            ->where('id', '=', $jobPostingId)
            ->first();

        if (!$jobPosting) {
            throw new InputException(trans('response.not_found'));
        }

        return $jobPosting;
    }
}
