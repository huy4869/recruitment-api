<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
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
}
