<?php

namespace App\Services\Recruiter\Application;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Helpers\ResponseHelper;
use App\Helpers\UserHelper;
use App\Jobs\Recruiter\ApplicationInterviewOnline;
use App\Models\Application;
use App\Models\MInterviewApproach;
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
            'user' => fn($q) => $q->withTrashed(),
            'applicationUserTrash',
            'applicationUserWorkHistories' => function ($query) {
                $query->withTrashed()
                    ->orderByRaw('period_end is not null, period_end DESC, period_start DESC');
            },
            'applicationUserLearningHistories' => function ($query) {
                $query->withTrashed()
                    ->orderByRaw('enrollment_period_start ASC, enrollment_period_end ASC');
            },
            'applicationUserLicensesQualifications' => function ($query) {
                $query->withTrashed()
                    ->orderByRaw('new_issuance_date ASC, created_at ASC');
            },
        ])
            ->withTrashed()
            ->where('id', $applicationId)
        ->first();

        if (!$application) {
            return null;
        }

        $masterData = UserHelper::getMasterDataWithUser();

        return self::addFormatUserProfileJsonData($application, $masterData);
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
                    '%s～%s%s',
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_start),
                    DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_end),
                    @$learningHistory->learningStatus->name ? trans('common.learning_status_name', ['status_name' => $learningHistory->learningStatus->name]) : null,
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
            'avatar_details' => $application->applicationUser->avatarDetails ?? [],
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
            ->with([
                'user' => function ($q) {
                    $q->withTrashed();
                },
                'storeAcceptTrashed',
                'applicationUserTrash',
                'applicationUserTrash.avatarDetails',
                'applicationUserTrash.avatarBanner',
                'applicationUserTrash.gender',
                'applicationUserTrash.province',
                'applicationUserTrash.provinceCity',
                'applicationUserTrash.province.provinceDistrict',
                'jobPosting' => function ($q) {
                    $q->withTrashed();
                },
                'interviews',
            ])
            ->withTrashed()
            ->first();

        if (!$application) {
            return null;
        }

        $application->interview_statuses = MInterviewStatus::query()
            ->whereNotIn('id', $application->interview_status_id == MInterviewStatus::STATUS_CANCELED ? [] : [MInterviewStatus::STATUS_CANCELED])
            ->get()
            ->map(function ($query) {
                return [
                    'id' => $query->id,
                    'name' => $query->name
                ];
            });
        $beReadApplications = $recruiter->be_read_applications ?? [];
        $beReadApplications = array_unique(array_merge($beReadApplications, [$id]));

        $recruiter->update([
            'be_read_applications' => $beReadApplications
        ]);

        return $application;
    }

    /**
     * @param $id
     * @param $data
     * @return bool|array
     * @throws InputException
     * @throws Exception
     */
    public function update($id, $data)
    {
        $recruiter = $this->user;

        $application = Application::query()
            ->where('id', $id)
            ->with([
                'user' => function ($q) {
                    $q->withTrashed();
                },
                'jobPostingAcceptTrashed',
                'storeAcceptTrashed' => function ($query) use ($recruiter) {
                    $query->where('user_id', $recruiter->id);
                },
                'interviews'
            ])
            ->withTrashed()
            ->first();

        if (!is_null($application->jobPostingAcceptTrashed->deleted_at) || !is_null($application->storeAcceptTrashed->deleted_at)) {
            return $application;
        }

        if (!is_null($application->deleted_at)) {
            return null;
        }

        $data['update_times'] = now();

        try {
            DB::beginTransaction();

            if ($application->interview_status_id != $data['interview_status_id']) {
                Notification::query()->create([
                    'user_id' => $application->user_id,
                    'notice_type_id' => Notification::TYPE_INTERVIEW_CHANGED,
                    'noti_object_ids' => [
                        'store_id' => $application->store_id,
                        'application_id' => $application->id,
                        'user_id' => $this->user->id,
                        'job_id' => $application->job_posting_id
                    ],
                    'title' => trans('notification.N006.title', [
                        'store_name' => $application->store->name,
                    ]),
                    'content' => trans('notification.N006.content', [
                        'store_name' => $application->store->name,
                        'interview_status' => MInterviewStatus::where('id', $data['interview_status_id'])->first()->name,
                    ]),
                ]);
            }

            if ($application->interview_approach_id != MInterviewApproach::STATUS_INTERVIEW_ONLINE) {
                $data['meet_link'] = null;
            }

            $application->update($data);

            DB::commit();
            return $application;
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
