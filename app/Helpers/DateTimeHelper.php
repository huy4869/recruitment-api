<?php

namespace App\Helpers;

use App\Models\JobPosting;
use Carbon\Carbon;

class DateTimeHelper
{

    /**
     * Get day of week
     *
     * @param $date
     * @return false
     */
    public static function firstDayOfWeek($date)
    {
        if (!$date) {
             return false;
        }

        $numberDay = Carbon::parse($date)->dayOfWeek;

        if ($numberDay != 0) {
            return false;
        }

        return $date;
    }

    /**
     * Format day of week
     *
     * @param $date
     * @return string
     */
    public static function formatDayOfMothFe($date)
    {
        $date = Carbon::parse($date);
        $day = $date->format(config('date.month_day'));
        $dayOfWeek = config('date.day_of_week_ja.' . $date->dayOfWeek);


        return sprintf('%s （%s）', $day, $dayOfWeek);
    }

    /**
     * Format date
     *
     * @param $date
     * @return string|null
     */
    public static function formatDate($date)
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)->format(config('date.fe_date_format'));
    }

    /**
     * Format datetime
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateTime($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format(config('date.fe_date_time_format'));
    }

    /**
     * Format datetime
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateTimeFull($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format(config('date.fe_date_time_full_format'));
    }

    /**
     * Format datetime japan
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateTimeJa($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format(config('date.fe_date_time_ja_format'));
    }

    /**
     * Format date japan
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateJa($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format(config('date.fe_date_ja_format'));
    }

    /**
     * Format date japan
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateHalfJa($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format(config('date.fe_date_half_ja_format'));
    }

    /**
     * Format date japan
     *
     * @param $year
     * @param $month
     * @return string|null
     */
    public static function formatNameDateHalfJa($year, $month)
    {
        if (!$year || !$month) {
            return null;
        }

        return $year . '年' . $month . '月';
    }

    /**
     * Format date japan fe
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateHalfJaFe($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format(config('date.fe_date_work_history_format'));
    }

    /**
     * Format date time half japan
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateTimeHalfJa($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->format(config('date.fe_date_time_half_ja_format'));
    }

    /**
     * @param $hour
     * @return string|null
     */
    public static function formatHour($hour)
    {
        if (empty($hour)) {
            return null;
        }

        return $hour . trans('user.fe_hour_format');
    }

    /**
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateDayOfWeekTimeJa($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        $dateTime = Carbon::parse($dateTime);
        $date = $dateTime->format(config('date.fe_date_ja_format'));
        $dayOfWeek = config('date.day_of_week_ja.' . $dateTime->dayOfWeek);
        $time = $dateTime->format('H:i');

        return sprintf('%s（%s）%s', $date, $dayOfWeek, $time);
    }

    /**
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateDayOfWeekJa($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        $dateTime = Carbon::parse($dateTime);
        $date = $dateTime->format(config('date.fe_date_ja_format'));
        $dayOfWeek = config('date.day_of_week_ja.' . $dateTime->dayOfWeek);

        return sprintf('%s（%s）', $date, $dayOfWeek);
    }

    /**
     * Format dateTime Be
     *
     * @param $dateTime
     * @return string|null
     */
    public static function formatDateWorkHistoryBe($dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::createFromFormat(config('date.fe_date_work_history_format'), $dateTime)->toDateString();
    }

    public static function formatTimeChat($dataTime)
    {
        if (!$dataTime) {
            return null;
        }

        $time = new Carbon($dataTime);
        $now = new Carbon(Carbon::now());
        $minute = $time->diffInMinutes($now);
        $hour = $time->diffInHours($now);

        if ($minute < config('date.less_than_hour')) {
            $date = ($minute != config('date.zero_minute')) ? $minute . trans('common.minute') : '';
        } elseif ($hour >= config('date.more_than_hour')  && $hour < config('date.less_than_date')) {
            $date = $hour . trans('common.hour');
        } else {
            $date = $time->format(config('date.hour'));
        }

        return $date;
    }

    public static function formatYearMonthChat($dataTime)
    {
        if (!$dataTime) {
            return null;
        }

        $time = new Carbon($dataTime);
        $now = new Carbon(Carbon::now());
        $minute = $time->diffInMinutes($now);
        $hour = $time->diffInHours($now);

        if ($minute < config('date.less_than_hour')) {
            $date = ($minute != config('date.zero_minute')) ? $minute . trans('common.minute') : '';
        } elseif ($hour >= config('date.more_than_hour')  && $hour < config('date.less_than_date')) {
            $date = $hour . trans('common.hour');
        } else {
            $date = $dataTime->format(config('date.month_day'));
        }

        return $date;
    }

    public static function checkDateLoginAt($dataTime)
    {
        if (!$dataTime) {
            return null;
        }

        $time = new Carbon($dataTime);
        $now = now();
        $minute = $time->diffInMinutes($now);
        $hour = $time->diffInHours($now);
        $week = $time->diffInDays($now);
        $dayOfWeek = config('date.day_of_week_ja.' . $time->dayOfWeek);
        $formatDate = $time->format(config('date.fe_date_ja_format'));
        $formatTime = $time->format(config('date.hour'));

        if ($minute < config('date.less_than_hour')) {
            $date = ($minute != config('date.zero_minute')) ? $minute . trans('common.minute') : config('date.one_minute') . trans('common.minute');
        } elseif ($hour >= config('date.more_than_hour')  && $hour < config('date.less_than_date')) {
            $date = $hour . trans('common.hour');
        } elseif ($week < config('date.week')) {
            $date = $time->diffInDays($now) . trans('common.before_day');
        } elseif ($week == config('date.week')) {
            $date = $time->diffInWeeks($now) . trans('common.week');
        } else {
            $date = sprintf('%s （%s） %s', $formatDate, $dayOfWeek, $formatTime);
        }

        return $date;
    }
    /**
     * Format date time for notification
     *
     * @return string|null
     */
    public static function formatTimeNotification($dateTime = null, $format = 'Y-m-d H:i:s')
    {
        Carbon::setLocale(config('app.locale'));

        if (!$dateTime) {
            return null;
        }

        if ($dateTime >= now()->subDays()) {
            return Carbon::createFromFormat($format, $dateTime)->diffForHumans();
        }

        return self::formatDateDayOfWeekTimeJa($dateTime);
    }

    /**
     * Parse To DiffForHumans japan
     *
     * @param null $dateTime
     * @return string
     */
    public static function parseToDiffForHumansJa($dateTime = null, $format = 'Y-m-d H:i:s')
    {
        Carbon::setLocale(config('app.locale'));
        if (!$dateTime) {
            return null;
        }

        if ($dateTime > now()->subDays(config('date.week'))) {
            return Carbon::createFromFormat($format, $dateTime)->diffForHumans();
        }

        return self::formatDateDayOfWeekTimeJa($dateTime);
    }

    /**
     * format month year
     *
     * @param $date
     * @return string
     */
    public static function formatMonthYear($date)
    {
        if ($date) {
            $month = substr($date, 4);
            $year = substr($date, 0, 4);

            return sprintf('%s%s%s%s', $year, trans('common.year'), $month, trans('common.month'));
        }
    }

    /**
     * @return string
     */
    public static function getTime()
    {
        $now = now();
        $hour = $now->hour;
        $minute = $now->minute;

        if ($minute > 0 && $minute < 30) {
            $minute = '30';
        } else {
            $hour += 1;
            $minute = '00';
        }

        return sprintf('%s:%s', substr("0{$hour}", -2), $minute);
    }

    public static function formatDateStartEnd($dateStart, $dateEnd)
    {
        if ($dateEnd) {
            return sprintf('%s～%s', self::formatMonthYear($dateStart), self::formatMonthYear($dateEnd));
        }

        return sprintf('%s～%s', self::formatMonthYear($dateStart), trans('common.now'));
    }

    public static function getHoursMinute($time)
    {
        $timeHoursMinute = Carbon::parse($time);

        return [
            'hours' => $timeHoursMinute->format(config('date.time_hours.hours')),
            'minute' => $timeHoursMinute->format(config('date.time_hours.minute')),
        ];
    }

    public static function birthDayByAge($dateBirthDay, $date)
    {
        if (!$dateBirthDay || !$date) {
            return null;
        }

        return Carbon::parse($dateBirthDay)->diff($date)->y;
    }

    public static function getStartEndWorkTime($start, $end, $startWorkType, $endWorkType, $rangeHoursType)
    {
        if ($rangeHoursType == JobPosting::HALF_DAY) {
            $startHoursMinute = DateTimeHelper::getHoursMinute($start);
            $endHoursMinute = DateTimeHelper::getHoursMinute($end);
            $hourStart = ltrim($startHoursMinute['hours'], '0');
            $hourEnd = ltrim($endHoursMinute['hours'], '0');
            $oneHourStartMorning = __('job_posting.morning.one_hours', ['hours' => $hourStart]);
            $halfHourStartMorning = __('job_posting.morning.half_hours', ['hours' => $hourStart]);
            $oneHourStartAfternoon = __('job_posting.afternoon.one_hours', ['hours' => $hourStart]);
            $halfHourStartAfternoon = __('job_posting.afternoon.half_hours', ['hours' => $hourStart]);
            $oneHourEndMorning = __('job_posting.morning.one_hours', ['hours' => $hourEnd]);
            $halfHourEndMorning = __('job_posting.morning.half_hours', ['hours' => $hourEnd]);
            $oneHourEndAfternoon = __('job_posting.afternoon.one_hours', ['hours' => $hourEnd]);
            $halfHourEndAfternoon = __('job_posting.afternoon.half_hours', ['hours' => $hourEnd]);

            if ($startWorkType == JobPosting::TYPE_MORNING && $endWorkType == JobPosting::TYPE_MORNING) {
                $start = $startHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourStartMorning : $oneHourStartMorning;
                $end = $endHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourEndMorning : $oneHourEndMorning;
            } elseif ($startWorkType == JobPosting::TYPE_AFTERNOON && $endWorkType == JobPosting::TYPE_AFTERNOON) {
                $start = $startHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourStartAfternoon : $oneHourStartAfternoon;
                $end = $endHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourEndAfternoon : $oneHourEndAfternoon;
            } elseif ($startWorkType == JobPosting::TYPE_MORNING && $endWorkType == JobPosting::TYPE_AFTERNOON) {
                $start = $startHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourStartMorning : $oneHourStartMorning;
                $end = $endHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourEndAfternoon : $oneHourEndAfternoon;
            } else {
                $start = $startHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourStartAfternoon : $oneHourStartAfternoon;
                $end = $endHoursMinute['minute'] == config('date.thirty_minutes') ? $halfHourEndMorning : $oneHourEndMorning;
            }
        }//end if

        return [
          'start' => $start,
          'end' => $end,
        ];
    }
}
