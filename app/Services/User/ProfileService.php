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
        $dateUser = $user->updated_at ? $user->updated_at->format(config('date.fe_date_format')) : null;
        $createdAt = $user->created_at ? $user->created_at->format(config('date.fe_date_ja_format')) : null;
        $date = max($dateLearningHistory, $dateQualification, $dateWorkHistory, $dateUser);
        $time = strtotime($date);
        $updatedAtNew = $date ? date(config('date.fe_date_ja_format'), $time) : $createdAt;

        return [
            'updateDateNew' => $updatedAtNew,
            'baseInfo' => [
                'percent' => $baseInfo,
                'total' => config('percentage.information.total'),
            ],
            'workHistory' => [
                'percent' => $percentWorkHistory,
                'total' => config('percentage.work_history.total'),
            ],
            'selfPr' => [
                'percent' => $selfPr,
                'total' => config('percentage.pr.total'),
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
        $percent = config('percentage.information.total')/config('percentage.information.attribute_required');
        $name = self::getPercentNameUser($user->first_name, $user->last_name, $percent);
        $furiName = self::getPercentNameUser($user->furi_first_name, $user->furi_last_name, $percent);
        $birthday = self::getPercentUser($user->birthday, $percent);
        $genderId = self::getPercentUser($user->gender_id, $percent);
        $tel = self::getPercentUser($user->tel, $percent);
        $email = self::getPercentUser($user->email, $percent);
        $provinceId = self::getPercentUser($user->province_id, $percent);
        $provinceCityId = self::getPercentUser($user->province_city_id, $percent);
        $address = self::getPercentUser($user->address, $percent);

        return round($name + $furiName + $birthday + $genderId + $tel + $email + $provinceId + $address + $provinceCityId);
    }

    /**
     * @param $user
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getPercentSelfPR($user)
    {
        $favorite = self::getPercentUser($user->favorite_skill, config('percentage.pr.attribute.favorite'));
        $skill = self::getPercentUser($user->self_pr, config('percentage.pr.attribute.self_pr'));
        $experience = self::getPercentUser($user->experience_knowledge, config('percentage.pr.attribute.experience'));

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
                if ($value['school_name'] && $value['enrollment_period_start'] && $value['enrollment_period_end']) {
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

    public function getPercentNameUser($firstValue, $lastValue, $percent)
    {
        if ($firstValue && $lastValue) {
            return $percent;
        }

        return config('percentage.default');
    }
}
