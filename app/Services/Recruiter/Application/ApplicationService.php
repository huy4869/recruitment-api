<?php

namespace App\Services\Recruiter\Application;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Helpers\UserHelper;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApplicationService extends Service
{
    public function profileUser($applicationId)
    {
        $application = Application::with([
            'user',
            'applicationUser',
            'applicationUserWorkHistories' => function ($query) {
                $query->orderByRaw('period_end is not null, period_start DESC, period_end DESC');
            },
            'applicationUserLearningHistories' => function ($query) {
                $query->orderByRaw('enrollment_period_start ASC, enrollment_period_end ASC');
            },
            'applicationUserLicensesQualifications' => function ($query) {
                $query->orderByRaw('new_issuance_date ASC, created_at ASC');
            },
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
                'work_time' => DateTimeHelper::formatDateStartEnd($workHistory->period_start, $workHistory->period_end),
                'job_types' => $workHistory->jobType->name,
                'positionOffices' => JobHelper::getTypeName($workHistory->position_office_ids, $masterData['masterPositionOffice']),
                'work_type' => $workHistory->workType->name,
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
        $fullAddress = sprintf(
            '〒 %s %s%s%s%s',
            @$application->applicationUser->postal_code,
            @$application->applicationUser->province->name,
            @$application->applicationUser->provinceCity->name,
            @$application->applicationUser->address,
            @$application->applicationUser->building,
        );

        return array_merge($application->toArray(), [
            'avatar_banner' => FileHelper::getFullUrl($application->applicationUser->avatarBanner->url ?? null),
            'avatar_details' => $application->applicationUser->avatarDetails,
            'last_login_at' => $application->user->last_login_at,
            'province' => $application->applicationUser->province->name ?? null,
            'province_city_name' => $application->applicationUser->provinceCity->name ?? null,
            'gender' => $application->applicationUser->gender->name ?? null,
            'applicationUserWorkHistories' => $applicationUserWorkHistories,
            'favorite_skill' => $application->favorite_skill,
            'experience_knowledge' => $application->experience_knowledge,
            'self_pr' => $application->self_pr,
            'applicationLearningHistories' => $applicationLearningHistories,
            'applicationLicensesQualifications' => $applicationLicensesQualifications,
            'full_address' => $fullAddress,
        ]);
    }

    /**
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function getDetail($id)
    {
        $recruiter = $this->user;

        $application = Application::query()
            ->where('id', $id)
            ->whereHas('store', function ($query) use ($recruiter) {
                $query->where('user_id', $recruiter->id);
            })
            ->with([
                'applicationUser',
                'applicationUser.avatarDetails',
                'applicationUser.avatarBanner',
                'applicationUser.gender',
                'applicationUser.province',
                'applicationUser.provinceCity',
                'applicationUser.province.provinceDistrict',
                'jobPosting',
                'interviews',
            ])
            ->first();

        if (!$application) {
            throw new InputException(trans('response.not_found'));
        }

        $beReadApplications = $recruiter->be_read_applications;
        $beReadApplications = array_unique(array_merge($beReadApplications, [$id]));

        $recruiter->update([
            'be_read_applications' => $beReadApplications
        ]);

        return $application;
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * @throws InputException
     * @throws Exception
     */
    public function update($id, $data)
    {
        $recruiter = $this->user;

        $application = Application::query()
            ->where('id', $id)
            ->whereHas('store', function ($query) use ($recruiter) {
                $query->where('user_id', $recruiter->id);
            })
            ->with([
                'store',
                'interviews'
            ])
            ->first();

        if (!$application || (
            $application->interview_status_id == MInterviewStatus::STATUS_REJECTED
            && $data['interview_status_id'] != MInterviewStatus::STATUS_REJECTED
        )) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            $application->update($data);

            $userNotifyData = [
                'user_id' => $application->user_id,
                'notice_type_id' => Notification::TYPE_INTERVIEW_CHANGED,
                'noti_object_ids' => [
                    'store_id' => $application->store_id,
                    'application_id' => $application->id,
                    'user_id' => $this->user->id
                ],
                'title' => trans('notification.N006.title', [
                    'store_name' => $application->store->name,
                ]),
                'content' => trans('notification.N006.content', [
                    'store_name' => $application->store->name,
                    'interview_status' => $application->interviews->name,
                ]),
            ];

            Notification::create($userNotifyData);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }

    /**
     * @return array
     */
    public static function getApplicationStatusIds()
    {
        return MInterviewStatus::query()->pluck('id')->toArray();
    }
}
