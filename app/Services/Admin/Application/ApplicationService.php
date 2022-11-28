<?php

namespace App\Services\Admin\Application;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Helpers\UserHelper;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use App\Services\Service;
use App\Services\User\Job\JobService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService extends Service
{
    /**
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function getDetail($id)
    {
        $admin = $this->user;
        $application = Application::query()
            ->where('id', $id)
            ->with([
                'store',
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

        if ($application) {

            $beReadApplications = $admin->be_read_applications;
            $beReadApplications = array_unique(array_merge($beReadApplications, [$id]));

            $admin->update([
                'be_read_applications' => $beReadApplications
            ]);

            return $application;
        }

        throw new InputException(trans('response.not_found'));
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
        $application = Application::query()
            ->where('id', $id)
            ->with([
                'store',
                'interviews',
                'store.owner.recruiterOffTimes',
                'store.owner.stores.applications',
            ])
            ->first();

        if (!$application) {
            throw new InputException(trans('response.not_found'));
        }

        if ($data['date'] && $data['hours']) {
            $this->hasDateApplication($application, $data);
        }

        return $this->updateApplication($application, $data);
    }

    /**
     * @param $application
     * @param $data
     * @return bool
     * @throws InputException
     * @throws ValidationException
     * @throws Exception
     */
    public function hasDateApplication($application, $data)
    {
        $date = $data['date'];
        $hours = $data['hours'];

        $now = now()->format('Y-m-d');
        $dateApplication = explode(' ', $application->date)[0];
        $hoursApplication = $application->hours;

        if ($this->checkTimeUpdate($now, $date, $hours, $dateApplication, $hoursApplication)) {
            throw ValidationException::withMessages([
                'date' => trans('validation.ERR.037')
            ]);
        }

        if ($date == $dateApplication && $hours == $hoursApplication) {
            return true;
        }

        $applications = Application::query()
            ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW])
            ->where('id', '!=', $application->id)
            ->whereDate('date', $date)
            ->where('hours', '=', $hours)
            ->where(function ($query) use ($application) {
                $query->where('user_id', '=', $application->user_id)
                    ->orWhere('job_posting_id', '=', $application->job_posting_id);
            })
            ->exists();

        if ($applications) {
            throw new InputException(trans('validation.ERR.036'));
        }

        $storeIds = $application->store->owner->stores->pluck('id')->toArray();
        $recruiterInterviewApplications = Application::query()
            ->whereIn('store_id', $storeIds)
            ->where('date', '=', $date . ' 00:00:00')
            ->where('hours', '=', $hours)
            ->where('id', '!=', $application->id)
            ->whereIn('interview_status_id', [MInterviewStatus::STATUS_APPLYING, MInterviewStatus::STATUS_WAITING_INTERVIEW])
            ->exists();

        if ($recruiterInterviewApplications) {
            throw new InputException(trans('validation.ERR.036'));
        }

        $month = Carbon::parse($data['date'])->firstOfMonth()->format('Y-m-d');
        $recruiterOffTimes = $application->store->owner->recruiterOffTimes->off_times ?? [];
        $recruiterOffTimes = $recruiterOffTimes[$month];
        $dataHours = preg_grep('/' . $date . '/i', $recruiterOffTimes);

        if (isset($dataHours[$date . ' ' . $hours])) {
            throw new InputException(trans('validation.ERR.036'));
        }
    }

    /**
     * @param $application
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function updateApplication($application, $data)
    {
        if ($application->interview_status_id != $data['interview_status_id']) {
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
        }

        try {
            DB::beginTransaction();

            $application->update($this->saveMakeData($data, $application));
            Notification::query()->create($userNotifyData);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }

    /**
     * @param $hours
     * @param $now
     * @param $date
     * @param $dateApplication
     * @param $hoursApplication
     * @return false
     */
    public function checkTimeUpdate($now, $date, $hours, $dateApplication, $hoursApplication)
    {
        $timeNow = DateTimeHelper::getTime();
        $checkTime = in_array($timeNow, config('date.time'));

        if (($date == $now && (!$checkTime || $hours <= $timeNow))
            || ($date < $now && !($date == $dateApplication && $hours == $hoursApplication))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Save make data
     *
     * @param $data
     * @return array
     */
    public function saveMakeData($data, $application)
    {
        return [
            'interview_approach_id' => $data['interview_approach_id'] ?? $application->interview_approach_id,
            'date' => $data['date'] ?? $application->date,
            'hours' => $data['hours'] ?? $application->hours,
            'note' => $data['note'] ?? $application->note,
            'interview_status_id' => $data['interview_status_id'],
            'owner_memo' => $data['owner_memo'],
            'update_times' => now(),
        ];
    }

    public function profileUser($applicationId)
    {
        $application = Application::with([
            'user',
            'applicationUser',
            'applicationUser.avatarDetails',
            'applicationUser.avatarBanner',
            'applicationUserWorkHistories' => function ($query) {
                $query->orderByRaw('period_end is not null, period_start DESC, period_end DESC');
            },
            'applicationUserLearningHistories' => function ($query) {
                $query->orderByRaw('enrollment_period_start ASC, enrollment_period_end ASC');
            },
            'applicationUserLicensesQualifications' => function ($query) {
                $query->orderByRaw('new_issuance_date ASC, created_at ASC');
            },
        ])
            ->where('id', $applicationId)
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
                'job_types' => @$workHistory->jobType->name,
                'positionOffices' => JobHelper::getTypeName($workHistory->position_office_ids, $masterData['masterPositionOffice']),
                'work_type' => @$workHistory->workType->name,
            ];
        }

        $applicationLearningHistories = [];

        foreach ($application->applicationUserLearningHistories as $learningHistory) {
            $applicationLearningHistories[] = [
                'school_name' => $learningHistory->school_name,
                'time_start_end' => sprintf(
                    '%s ～ %s(%s)',
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

        $url = [];

        foreach ($application->applicationUser->avatarDetails as $avatar) {
            $url[] = (object)['url' => FileHelper::getFullUrl($avatar->url)];
        }

        $fullAddress = sprintf(
            '〒 %s %s%s%s%s',
            @$application->applicationUser->postal_code,
            @$application->applicationUser->province->name,
            @$application->applicationUser->provinceCity->name,
            @$application->applicationUser->address,
            @$application->applicationUser->building,
        );
        $applicationUser = $application->applicationUser;

        return [
            'id' => $application->user_id,
            'avatar_banner' => FileHelper::getFullUrl($application->applicationUser->avatarBanner->url ?? null),
            'avatar_details' => $url,
            'first_name' => $applicationUser->first_name,
            'last_name' => $applicationUser->last_name,
            'furi_first_name' => $applicationUser->furi_first_name,
            'furi_last_name' => $applicationUser->furi_last_name,
            'alias_name' => $applicationUser->alias_name,
            'age' => $applicationUser->age,
            'user_address' => [
                'postal_code' => $applicationUser->postal_code,
                'province_id' => $applicationUser->province_id,
                'province_name' => @$applicationUser->province->name,
                'province_city_id' => $applicationUser->province_city_id,
                'province_city_name' => @$applicationUser->provinceCity->name,
                'address' => $applicationUser->address,
                'building' => $applicationUser->building,
            ],
            'tel' => $applicationUser->tel,
            'email' => $applicationUser->email,
            'last_login_at' => DateTimeHelper::checkDateLoginAt($application->user->last_login_at),
            'facebook' => $application->facebook,
            'twitter' => $application->twitter,
            'instagram' => $application->instagram,
            'line' => $application->line,
            'birthday' => DateTimeHelper::formatDateJa($applicationUser->birthday),
            'gender_id' => $applicationUser->gender_id ?? null,
            'gender' => $applicationUser->gender->name ?? null,
            'user_work_histories' => $applicationUserWorkHistories,
            'pr' => [
                'favorite_skill' => $applicationUser->favorite_skill,
                'experience_knowledge' => $applicationUser->experience_knowledge,
                'self_pr' => $applicationUser->self_pr,
                'skills' => UserHelper::getSkillUser($applicationUser->skills),
            ],
            'user_learning_histories' => $applicationLearningHistories,
            'user_licenses_qualifications' => $applicationLicensesQualifications,
            'motivation' => [
                'motivation' => $applicationUser->motivation,
                'noteworthy' => $applicationUser->noteworthy,
            ],
        ];
    }
}
