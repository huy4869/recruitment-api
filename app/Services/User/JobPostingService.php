<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\MInterviewApproach;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JobPostingService extends Service
{
    /**
     * Check Time Date
     *
     * @param $id
     * @param null $application
     * @return array
     * @throws InputException
     */
    public function detailJobUserApplication($id, $application = null)
    {
        $jobPosting = $this->checkJobPosting($id);

        $applicationsTime = $jobPosting->applications
            ->where('user_id', '!=', $this->user->id)
            ->whereIn('interview_status_id', [Application::STATUS_APPLYING, Application::STATUS_WAITING_INTERVIEW]);
        $userApplicationsTime = Application::query()
            ->where('user_id', $this->user->id)
            ->where('job_posting_id', '!=', $jobPosting->id)
            ->whereDate('date', '>=', now())
            ->whereIn('interview_status_id', [Application::STATUS_APPLYING, Application::STATUS_WAITING_INTERVIEW])
            ->get();
        $recruiterOffTimes = $jobPosting->store->createdBy->recruiterOffTimes->off_times ?? [];
        $monthNow = now()->firstOfMonth()->format('Y-m-d');
        $monthDay = now()->addDays(config('date.max_day'))->firstOfMonth()->format('Y-m-d');

        return $this->resultDate(
            self::resultApplication($applicationsTime),
            self::resultApplication($userApplicationsTime),
            self::resultRecruiterOffTimes([$monthNow, $monthDay], $recruiterOffTimes),
            $application
        );
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
     * @param $applicationsTime
     * @param $userApplicationsTime
     * @param $recruiterOffTimes
     * @param $application
     * @return mixed
     */
    public function resultDate($applicationsTime, $userApplicationsTime, $recruiterOffTimes, $application = null)
    {
        $dateStart = [];
        $i = 0;

        while ($i <= config('date.max_date')) {
            $dateCheck = now()->addDays($i)->format('Y-m-d');
            $applicationsTimes = $applicationsTime[$dateCheck] ?? [];
            $userApplicationsTimes = $userApplicationsTime[$dateCheck] ?? [];
            $timeChecks = array_merge($applicationsTimes, $userApplicationsTimes);
            $dataHours = preg_grep('/' . $dateCheck . '/i', $recruiterOffTimes);

            foreach ($dataHours as $dataHour) {
                $timeChecks[] = explode(' ', $dataHour)[1];
            }

            if ($application && $application->date == now()->format('Y-m-d 00:00:00')) {
                $times = $this->checkTime($dateCheck, $timeChecks, $application->hours);
            } else {
                $times = $this->checkTime($dateCheck, $timeChecks);
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
    public function checkTime($date, $timeCoincides = [], $hours = null)
    {
        $data = [];
        $isEnabledDate = false;
        $currentHour = DateTimeHelper::getTime();
        $endTime = strtotime(date('Y-m-d' . config('date.time_max')));
        $checkTime = array_search($currentHour, config('date.time'));
        $checkTime = config('date.range_time') + ($checkTime ? $checkTime : 0);

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
     * Store user application
     *
     * @param $data
     * @return Builder|Model
     * @throws InputException
     */
    public function store($data)
    {
        $date = $data['date'];
        $hours = $data['hours'];
        $now = now()->format('Y-m-d');
        $user = $this->user;
        if (!in_array($data['hours'], config('date.time'))) {
            throw new InputException(trans('response.ERR.999'));
        }

        if ($date == $now && $this->checkTimeStore($hours)) {
            throw new InputException(trans('response.ERR.999'));
        }

        $jobPosting = $this->checkJobPosting($data['id']);

        $application = Application::query()
            ->where('user_id', '=', $user->id)
            ->where('job_posting_id', '=', $jobPosting->id)
            ->exists();

        if ($application) {
            throw new InputException(trans('response.not_found'));
        }

        $applications = Application::query()
            ->whereIn('interview_status_id', [Application::STATUS_APPLYING, Application::STATUS_WAITING_INTERVIEW])
            ->whereDate('date', $date)
            ->where('hours', '=', $hours)
            ->get()
            ->pluck('user_id', 'job_posting_id')
            ->toArray();

        if (in_array($user->id, $applications) || in_array($jobPosting->id, array_keys($applications))) {
            throw new InputException(trans('response.ERR.999'));
        }

        $month = Carbon::parse($data['date'])->firstOfMonth()->format('Y-m-d');
        $recruiterOffTimes = $jobPosting->store->createdBy->recruiterOffTimes->off_times ?? [];
        $recruiterOffTimes = self::resultRecruiterOffTimes([$month], $recruiterOffTimes);
        $dataHours = preg_grep('/' . $date . '/i', $recruiterOffTimes);

        foreach ($dataHours as $dataHour) {
            if ($hours == explode(' ', $dataHour)[1]) {
                throw new InputException(trans('response.ERR.999'));
            }
        }

        return Application::query()->create($this->makeSaveData($jobPosting, $data));
    }

    /**
     * Save make dat
     *
     * @param $jobPosting
     * @param $data
     * @return array
     * @throws InputException
     */
    public function makeSaveData($jobPosting, $data)
    {
        $interviewApproaches = MInterviewApproach::query()->where('id', $data['interview_approaches_id'])->first();

        if (!$interviewApproaches) {
            throw new InputException('response.not_found');
        }

        return [
            'user_id' => $this->user->id,
            'job_posting_id' => $jobPosting->id,
            'store_id' => $jobPosting->store_id,
            'interview_status_id' => Application::STATUS_APPLYING,
            'interview_approaches' => [
                'id' => $interviewApproaches->id,
                'approach' => $data['note'],
            ],
            'date' => $data['date'],
            'hours' => $data['hours'],
            'note' => '',
            'update_times' => now(),
        ];
    }

    /**
     * @param $hours
     * @return bool
     */
    public function checkTimeStore($hours)
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
     * Check job posting
     *
     * @param $jobPostingId
     * @return Builder|Model|object
     * @throws InputException
     */
    public function checkJobPosting($jobPostingId)
    {
        $jobPosting = JobPosting::query()
            ->released()
            ->with(['applications', 'store.createdBy.recruiterOffTimes'])
            ->where('id', '=', $jobPostingId)
            ->first();

        if (!$jobPosting) {
            throw new InputException(trans('response.not_found'));
        }

        return $jobPosting;
    }
}
