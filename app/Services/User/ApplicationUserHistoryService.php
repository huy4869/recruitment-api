<?php

namespace App\Services\User;

use App\Models\ApplicationUserLearningHistory;
use App\Models\ApplicationUserLicensesQualification;
use App\Models\ApplicationUserWorkHistory;
use App\Models\UserLearningHistory;
use App\Models\UserLicensesQualification;
use App\Models\UserWorkHistory;
use App\Services\Service;

class ApplicationUserHistoryService extends Service
{

    /**
     * Store application work histories
     *
     * @param $application
     * @return bool
     */
    public function storeApplicationWorkHistories($application)
    {
        $userWorkHistories = UserWorkHistory::query()->where('user_id', '=', $application->user_id)->get();
        $data = [];

        foreach ($userWorkHistories as $userWorkHistory) {
            $data[] = [
                'user_id' => $userWorkHistory->user_id,
                'job_posting_id' => $application->job_posting_id,
                'job_type_id' => $userWorkHistory->job_type_id,
                'store_name' => $userWorkHistory->store_name,
                'company_name' => $userWorkHistory->company_name,
                'period_start' => $userWorkHistory->period_start,
                'period_end' => $userWorkHistory->period_end,
                'position_office_ids' => $userWorkHistory->position_offices,
                'business_content' => $userWorkHistory->business_content,
                'experience_accumulation' => $userWorkHistory->experience_accumulation,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return ApplicationUserWorkHistory::query()->insert($data);
    }

    /**
     * Store application learning histories
     *
     * @param $application
     * @return bool
     */
    public function storeApplicationLearningHistories($application)
    {
        $userLearningHistories = UserLearningHistory::query()->where('user_id', '=', $application->user_id)->get();
        $data = [];

        foreach ($userLearningHistories as $userLearningHistory) {
            $data[] = [
                'user_id' => $userLearningHistory->user_id,
                'job_posting_id' => $application->job_posting_id,
                'learning_status_id' => $userLearningHistory->learning_status_id,
                'school_name' => $userLearningHistory->school_name,
                'enrollment_period_start' => $userLearningHistory->enrollment_period_start,
                'enrollment_period_end' => $userLearningHistory->enrollment_period_end,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return ApplicationUserLearningHistory::query()->insert($data);
    }

    /**
     * Store application licenses qualification histories
     *
     * @param $application
     * @return bool
     */
    public function storeApplicationLicensesQualificationHistories($application)
    {
        $userLicensesQualificationHistories = UserLicensesQualification::query()->where('user_id', '=', $application->user_id)->get();
        $data = [];

        foreach ($userLicensesQualificationHistories as $userLicensesQualificationHistory) {
            $data[] = [
                'user_id' => $userLicensesQualificationHistory->user_id,
                'job_posting_id' => $application->job_posting_id,
                'name' => $userLicensesQualificationHistory->name,
                'new_issuance_date' => $userLicensesQualificationHistory->new_issuance_date,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return ApplicationUserLicensesQualification::query()->insert($data);
    }
}
