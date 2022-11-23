<?php

namespace App\Services\Admin;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
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
                    ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW]);
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
            $applierId = '';
            $applierUserId = '';

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
                $applierId = '';
                $applierUserId = '';
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
        $userId = $data['user_id'];

        if (!in_array($data['hours'], config('date.time'))) {
            throw new InputException(trans('response.ERR.999'));
        }

        $application = Application::query()
            ->with(['store.owner.recruiterOffTimes', 'store.owner.stores.applications'])
            ->where('user_id', '=', $userId)
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
            return $application->update($data);
        }

        $applications = Application::query()
            ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW])
            ->where('id', '!=', $applicationId)
            ->whereDate('date', $date)
            ->where('hours', '=', $hours)
            ->get()
            ->pluck('user_id', 'job_posting_id')
            ->toArray();

        if (in_array($userId, $applications) || in_array($application->job_posting_id, array_keys($applications))) {
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

        if (isset($dataHours[$date . ' ' . $hours])) {
            throw new InputException(trans('validation.ERR.036'));
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

        foreach (config('date.time') as $key => $time) {
            if (($key < $checkTime && $hours == $time) || !$checkTime) {
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
                    ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW]);
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
}
