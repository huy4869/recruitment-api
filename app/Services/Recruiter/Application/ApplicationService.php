<?php

namespace App\Services\Recruiter\Application;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Helpers\UserHelper;
use App\Http\Resources\Recruiter\MultipleImageResoure;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\Image;
use App\Services\Service;

class ApplicationService extends Service
{
    public function profileUser($applicationId)
    {
        $application = Application::with([
            'user',
            'applicationUser',
            'applicationUserWorkHistories',
            'applicationUserLearningHistories',
            'applicationUserLicensesQualifications'
        ])->where('id', $applicationId)
        ->first();

        if ($application) {
            $masterData = UserHelper::getMasterDataWithUser();

            return self::addFormatUserProfileJsonData($application, $masterData);
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * format data
     *
     * @param $application
     * @param $masterData
     * @return array
     */
    public static function addFormatUserProfileJsonData($application, $masterData)
    {
        $applicationUserWorkHistories = [];
        foreach ($application->applicationUserWorkHistories as $workHistory) {
            $applicationUserWorkHistories[] = [
                'store_name' => $workHistory->store_name,
                'company_name' => $workHistory->company_name,
                'business_content' => $workHistory->business_content,
                'experience_accumulation' => $workHistory->experience_accumulation,
                'work_time' => sprintf(
                    '%s ~ %s',
                    DateTimeHelper::formatMonthYear($workHistory->period_start),
                    DateTimeHelper::formatMonthYear($workHistory->period_end)
                ),
                'job_types' => $workHistory->jobType->name,
                'positionOffice' => JobHelper::getTypeName($workHistory->position_office_ids, $masterData['masterPositionOffice']),
                'work_types' => $workHistory->workType->name,
            ];
        }

        $applicationLearningHistories = [];
        foreach ($application->applicationUserLearningHistories as $learningHistory) {
            $applicationLearningHistories[] = [
                'school_name' => $learningHistory->school_name,
                'time_start_end' => sprintf(
                    '%s ~ %s(%s)',
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_start),
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_end),
                    $learningHistory->learningStatus->name,
                ),
            ];
        }

        $applicationLicensesQualifications = [];
        foreach ($application->applicationUserLicensesQualifications as $applicationLicensesQualification) {
            $applicationLicensesQualifications[] = [
                'name' => $applicationLicensesQualification->name,
                'new_issuance_date' => DateTimeHelper::formatMonthYear($applicationLicensesQualification->new_issuance_date),
            ];
        }

        return array_merge($application->toArray(), [
            'avatar_banner' => FileHelper::getFullUrl($application->applicationUser->avatarBanner->url ?? null),
            'avatar_details' => $application->applicationUser->avatarDetails,
            'last_login_at' => $application->user->last_login_at,
            'province' => @$application->applicationUser->province->name,
            'district_name' => @$application->applicationUser->province->provinceDistrict->name,
            'gender' => @$application->applicationUser->gender->name,
            'applicationUserWorkHistories' => $applicationUserWorkHistories,
            'favorite_skill' => $application->favorite_skill,
            'experience_knowledge' => $application->experience_knowledge,
            'self_pr' => $application->self_pr,
            'applicationLearningHistories' => $applicationLearningHistories,
            'applicationLicensesQualifications' => $applicationLicensesQualifications,
        ]);
    }
}
