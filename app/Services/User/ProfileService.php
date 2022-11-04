<?php

namespace App\Services\User;

use App\Helpers\UserHelper;
use App\Services\Service;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;

class ProfileService extends Service
{
    /**
     * get % Profile
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getCompletionPercent()
    {
        $user = $this->user;
        $userInformation = $user->load('userLearningHistories', 'userLicensesQualifications', 'userWordHistories');

        $motivation = self::getPercentUser($user->motivation, config('percentage.motivation'));
        $baseInfo = self::getPercentBaseInfo($user);
        $percentageUserLearning = self::getPercentLearningHistories($userInformation->userLearningHistories);
        $qualification = $userInformation->userLicensesQualifications->first() ? config('percentage.motivation') : config('percentage.default');
        $percentWorkHistory = UserHelper::getPercentWorkHistory($userInformation->userWordHistories);
        $selfPr = self::getPercentSelfPR($user);

        $dateLearningHistory = UserHelper::getNewDate($userInformation->userLearningHistories);
        $dateQualification = UserHelper::getNewDate($userInformation->userLicensesQualifications);
        $dateWorkHistory = UserHelper::getNewDate($userInformation->userWordHistories);
        $dateUser = $user->updated_at ? $user->updated_at->format('Y/m/d') : null;
        $createdAt = $user->created_at ? $user->created_at->format(config('date.fe_date_ja_format')) : null;
        $date = max($dateLearningHistory, $dateQualification, $dateWorkHistory, $dateUser);
        $time = strtotime($date);
        $updatedAtNew = $date ? date(config('date.fe_date_ja_format'), $time) : $createdAt;

        return [
            'updateDateNew' => $updatedAtNew,
            'baseInfo' => [
                'percent' => $baseInfo,
                'total' => config('percentage.information'),
            ],
            'workHistory' => [
                'percent' => $percentWorkHistory,
                'total' => config('percentage.user_learning_history'),
            ],
            'selfPr' => [
                'percent' => $selfPr,
                'total' => config('percentage.pr'),
            ],
            'qualification' => [
                'percent' => $qualification,
                'total' => config('percentage.motivation'),
            ],
            'percentageUserLearning' => [
                'percent' => $percentageUserLearning,
                'total' => config('percentage.user_learning_history'),
            ],
            'motivation' => [
                'percent' => $motivation,
                'total' => config('percentage.motivation'),
            ],
        ];
    }

    /**
     * @param $user
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getPercentBaseInfo($user)
    {
        $firstName = self::getPercentUser($user->first_name, config('percentage.motivation'));
        $lastName = self::getPercentUser($user->last_name, config('percentage.motivation'));
        $furiFirst = self::getPercentUser($user->furi_first_name, config('percentage.motivation'));
        $furiLast = self::getPercentUser($user->furi_last_name, config('percentage.motivation'));
        $birthday = self::getPercentUser($user->birthday, config('percentage.motivation'));
        $genderId = self::getPercentUser($user->gender_id, config('percentage.motivation'));
        $tel = self::getPercentUser($user->tel, config('percentage.motivation'));
        $email = self::getPercentUser($user->email, config('percentage.motivation'));
        $provinceId = self::getPercentUser($user->province_id, config('percentage.motivation'));
        $city = self::getPercentUser($user->city, config('percentage.motivation'));

        return $firstName + $lastName + $furiFirst + $furiLast + $birthday + $genderId + $tel + $email + $provinceId + $city;
    }

    /**
     * @param $user
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getPercentSelfPR($user)
    {
        $favorite = self::getPercentUser($user->favorite_skill, config('percentage.favorite'));
        $skill = self::getPercentUser($user->self_pr, config('percentage.self_pr'));
        $experience = self::getPercentUser($user->experience_knowledge, config('percentage.experience'));

        return $favorite + $skill + $experience;
    }

    /**
     * check record
     *
     * @param $learningHistories
     * @return Repository|Application|mixed
     */
    public function getPercentLearningHistories($learningHistories)
    {
        if ($learningHistories) {
            foreach ($learningHistories as $value) {
                if ($value['learning_status_id'] && $value['school_name'] && $value['enrollment_period_start'] && $value['enrollment_period_end']) {
                    return config('percentage.user_learning_history');
                }
            }
        }

        return config('percentage.default');
    }


    /**
     * @param $value
     * @param $percent
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getPercentUser($value, $percent)
    {
        if ($value) {
            return $percent;
        }

        return config('percentage.default');
    }
}
