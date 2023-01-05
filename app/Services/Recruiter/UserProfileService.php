<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\CommonHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Helpers\UserHelper;
use App\Models\MPositionOffice;
use App\Models\User;
use App\Services\Service;

class UserProfileService extends Service
{
    /**
     * detail user
     *
     * @param $user_id
     * @return array
     * @throws InputException
     */
    public function detail($user_id)
    {
        $user = User::query()->with([
            'userLearningHistories' => function ($query) {
                $query->orderByRaw('enrollment_period_start ASC, enrollment_period_end ASC');
            },
            'userLicensesQualifications' => function ($query) {
                $query->orderByRaw('new_issuance_date ASC, created_at ASC');
            },
            'userWordHistories' => function ($query) {
                $query->orderByRaw('period_end is not null, period_start DESC, period_end DESC');
            },
            'avatarBanner',
            'avatarDetails'
            ])
            ->where('id', $user_id)
            ->roleUser()
            ->withTrashed()
            ->first();
        $masterData = UserHelper::getMasterDataWithUser();

        if (!$user) {
            return null;
        }

        return self::addFormatUserProfileJsonData($user, $masterData);
    }

    /**
     * get data MPositionOffice
     *
     * @return array
     */
    public static function getMasterDataPositionOffice()
    {
        $jobTypes = MPositionOffice::all();

        return CommonHelper::getMasterDataIdName($jobTypes);
    }

    /**
     * format data
     *
     * @param $user
     * @param $masterData
     * @return array
     */
    public static function addFormatUserProfileJsonData($user, $masterData)
    {
        $userWorkHistories = [];
        foreach ($user->userWordHistories as $workHistory) {
            $userWorkHistories[] = [
                'store_name' => $workHistory->store_name,
                'company_name' => $workHistory->company_name,
                'business_content' => $workHistory->business_content,
                'experience_accumulation' => $workHistory->experience_accumulation,
                'work_time' => DateTimeHelper::formatDateStartEnd($workHistory->period_start, $workHistory->period_end),
                'job_type' => @$workHistory->jobType->name,
                'positionOffices' => @JobHelper::getTypeName($workHistory->position_office_ids, $masterData['masterPositionOffice']),
                'work_type' => @$workHistory->workType->name,
            ];
        }

        $learningHistories = [];
        foreach ($user->userLearningHistories as $learningHistory) {
            $learningHistories[] = [
                'school_name' => $learningHistory->school_name,
                'time_start_end' => sprintf(
                    '%s～%s%s',
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_start),
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_end),
                    @$learningHistory->learningStatus->name ? trans('common.learning_status_name', ['status_name' => $learningHistory->learningStatus->name]) : null,
                ),
            ];
        }

        $licensesQualifications = [];
        foreach ($user->userLicensesQualifications as $userLicensesQualification) {
            $licensesQualifications[] = [
                'name' => $userLicensesQualification->name,
                'new_issuance_date' => DateTimeHelper::formatMonthYear($userLicensesQualification->new_issuance_date),
            ];
        }

        return array_merge($user->toArray(), [
            'avatar_banner' => FileHelper::getFullUrl($user->avatarBanner->url ?? null),
            'avatar_details' => $user->avatarDetails,
            'province_name' => $user->province->name ?? null,
            'province_city_name' => $user->provinceCity->name ?? null,
            'gender' => $user->gender->name ?? null,
            'user_work_histories' => $userWorkHistories,
            'user_learning_histories' => $learningHistories,
            'user_licenses_qualifications' => $licensesQualifications,
            'motivation' => $user->motivation,
        ]);
    }
}
