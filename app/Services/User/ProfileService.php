<?php

namespace App\Services\User;

use App\Helpers\UserHelper;
use App\Services\Service;

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
        $percentageUserLearning = $userInformation->userLearningHistories->first() ? config('percentage.user_learning_history') : config('percentage.default');
        $qualification = $userInformation->userLicensesQualifications->first() ? config('percentage.user_learning_history') : config('percentage.default');
        $percentWorkHistory = UserHelper::getPercentWorkHistory($userInformation->userWordHistories);
        $selfPr = self::getPercentSelfPR($user);

        $dateLearningHistory = UserHelper::getNewDate($userInformation->userLearningHistories);
        $dateQualification = UserHelper::getNewDate($userInformation->userLicensesQualifications);
        $dateWorkHistory = UserHelper::getNewDate($userInformation->userWordHistories);
        $dateUser = date_format($userInformation->updated_at, 'Y/m/d');
        $date = [];
        array_push($date, $dateLearningHistory, $dateQualification, $dateWorkHistory, $dateUser);
        $updatedAtNew = date(config('date.fe_date_ja_format'), max(array_map('strtotime', $date)));

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
                'total' => config('percentage.user_learning_history'),
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
