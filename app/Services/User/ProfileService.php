<?php

namespace App\Services\User;

use App\Helpers\UserHelper;
use App\Models\UserLearningHistory;
use App\Models\UserLicensesQualification;
use App\Models\UserWorkHistory;
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

        $favorite = $user->favorite_skill ? config('percentage.favorite') : config('percentage.default');
        $skill = $user->self_pr ? config('percentage.self_pr') : config('percentage.default');
        $experience = $user->experience_knowledge ? config('percentage.experience') : config('percentage.default');
        $motivation = $user->motivation ? config('percentage.motivation') : config('percentage.default');
        $profile = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'furi_first_name' => $user->furi_first_name,
            'furi_last_name' => $user->furi_last_name,
            'birthday' => $user->birthday,
            'gender_id' => $user->gender_id,
            'tel' => $user->tel,
            'province_id' => $user->province_id,
            'city' => $user->city,
        ];
        $baseInfo = UserHelper::getPercentage($profile, config('percentage.information'));

        if (is_null($userInformation->userLearningHistories->first())) {
            $percentageUserLearning = config('percentage.default');
        } else {
            $dataUserLearn = [
                'learning_status_id' => $userInformation->userLearningHistories->first()->learning_status_id,
                'school_name' => $userInformation->userLearningHistories->first()->school_name,
                'enrollment_period_start' => $userInformation->userLearningHistories->first()->enrollment_period_start,
                'enrollment_period_end' => $userInformation->userLearningHistories->first()->enrollment_period_end,
            ];
            $percentageUserLearning = UserHelper::getPercentage($dataUserLearn, config('percentage.user_learning_history'));
        }

        if (is_null($userInformation->userLicensesQualifications->first())) {
            $qualification = config('percentage.default');
        } else {
            $dataUserQualification = [
                'name' => $userInformation->userLicensesQualifications->first()->name,
                'new_issuance_date' => $userInformation->userLicensesQualifications->first()->new_issuance_date,
            ];
            $qualification = UserHelper::getPercentage($dataUserQualification, config('percentage.user_qualification'));
        }

        if (is_null($userInformation->userWordHistories->first())) {
            $workHistory = config('percentage.default');
        } else {
            $dataWorkHistory = [
              'job_type_id' => $userInformation->userWordHistories->first()->job_type_id,
              'store_name' => $userInformation->userWordHistories->first()->store_name,
              'position_offices' => $userInformation->userWordHistories->first()->position_offices,
              'period_start' => $userInformation->userWordHistories->first()->period_start,
              'period_end' => $userInformation->userWordHistories->first()->period_end,
            ];
            $percentageUserWorkHistory = UserHelper::getPercentage($dataWorkHistory, config('percentage.user_work_history'));
            $businessContent = $userInformation->userWordHistories->first()->business_content ? config('percentage.business_content') : config('percentage.default');
            $experienceAccumulation = $userInformation->userWordHistories->first()->experience_accumulation ? config('percentage.experience_accumulation') : config('percentage.default');
            $workHistory = $percentageUserWorkHistory + $businessContent + $experienceAccumulation;
        }

        $selfPr = $favorite + $skill + $experience;

        return [
            'baseInfo' => $baseInfo,
            'workHistory' => $workHistory,
            'selfPr' => $selfPr,
            'qualification' => $qualification,
            'percentageUserLearning' => $percentageUserLearning,
            'motivation' => $motivation,
        ];
    }
}
