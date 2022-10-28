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
            'userLearningHistories',
            'userLicensesQualifications',
            'userWordHistories',
            'avatarBanner',
            'avatarDetails'
            ])
            ->where('id', $user_id)
            ->roleUser()
            ->first();
        $masterData = UserHelper::getMasterDataWithUser();

        if ($user) {
            return self::addFormatUserProfileJsonData($user, $masterData);
        }

        throw new InputException(trans('validation.ERR.exist.user_not_exist'));
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
        $userWordHistories = [];
        foreach ($user->userWordHistories as $wordHistory) {
            $userWordHistories[] = [
                'store_name' => $wordHistory->store_name,
                'company_name' => $wordHistory->company_name,
                'business_content' => $wordHistory->business_content,
                'experience_accumulation' => $wordHistory->experience_accumulation,
                'work_time' => sprintf(
                    '%s ~ %s',
                    DateTimeHelper::formatMonthYear($wordHistory->period_start),
                    DateTimeHelper::formatMonthYear($wordHistory->period_end)
                ),
                'job_types' => $wordHistory->jobType->name,
                'positionOffice' => JobHelper::getTypeName($wordHistory->position_office_ids, $masterData['masterPositionOffice']),
                'work_types' => $wordHistory->workType->name,
            ];
        }

        $learningHistories = [];
        foreach ($user->userLearningHistories as $learningHistory) {
            $learningHistories[] = [
                'school_name' => $learningHistory->school_name,
                'time_start_end' => sprintf(
                    '%s ~ %s(%s)',
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_start),
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_end),
                    $learningHistory->learningStatus->name,
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
            'avatar_details' => $user->avatarDetails ?: null,
            'province' => $user->province->name,
            'district_name' => $user->province->provinceDistrict->name,
            'gender' => $user->gender->name ?? null,
            'user_word_histories' => $userWordHistories,
            'favorite_skill' => $user->favorite_skill,
            'experience_knowledge' => $user->experience_knowledge,
            'self_pr' => $user->self_pr,
            'user_learning_histories' => $learningHistories,
            'user_licenses_qualifications' => $licensesQualifications,
            'motivation' => $user->motivation,
        ]);
    }
}
