<?php

namespace App\Helpers;

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


        return sprintf('%s (%s)', $day, $dayOfWeek);
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
            $date = $minute . '分前';
        } elseif ($hour >= config('date.more_than_hour')  && $hour < config('date.less_than_date')) {
            $date = $dataTime->format('H:i');
        } else {
            $date = DateTimeHelper::formatDateTimeJa($dataTime);
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
            $date = $minute . '分前';
        } elseif ($hour >= config('date.more_than_hour')  && $hour < config('date.less_than_date')) {
            $date = $dataTime->format(config('date.hour'));
        } else {
            $date = $dataTime->format(config('date.month_day'));
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

        if ($dateTime->isToday()) {
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
        $month = substr($date, 4);
        $year = substr($date, 0, 4);

        return sprintf('%s%s%s%s', $year, trans('common.year'), $month, trans('common.month'));
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
}
