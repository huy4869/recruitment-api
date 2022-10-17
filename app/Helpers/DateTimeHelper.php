<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateTimeHelper
{
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

        return sprintf('%s (%s) %s', $date, $dayOfWeek, $time);
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
        $time = new Carbon($dataTime);
        $now = new Carbon(Carbon::now());

        if ($time->diffInMinutes($now) < config('date.less_than_hour')) {
            $date = $time->diffInMinutes($now) . '分前';
        } elseif ($time->diffInHours($now) >= config('date.more_than_hour')  && $time->diffInHours($now) < config('date.less_than_date')) {
            $date = $time->diffInHours($now) . '時間前';
        } else {
            $date = DateTimeHelper::formatDateTimeJa($dataTime);
        }

        return $date;
    }

    /**
     * Format date time for notification
     *
     * @return string|null
     */
    public static function formatTimeNotification($dateTime)
    {
        $dateTime = Carbon::parse($dateTime);

        if ($dateTime->isToday()) {
            return $dateTime->format(config('date.fe_data_time_hour'));
        }

        return self::formatDateDayOfWeekTimeJa($dateTime);
    }
}
