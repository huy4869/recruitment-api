<?php

namespace App\Services\User;

use App\Models\ApplicationUser;
use App\Models\ApplicationUserLearningHistory;
use App\Models\ApplicationUserLicensesQualification;
use App\Models\ApplicationUserWorkHistory;
use App\Models\Chat;
use App\Models\Image;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserLearningHistory;
use App\Models\UserLicensesQualification;
use App\Models\UserWorkHistory;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApplicationUserHistoryService extends Service
{

    /**
     * Store notification user application
     *
     * @param $application
     * @param $jobPosting
     * @return Builder|Model
     */
    public function storeNotifications($application, $jobPosting)
    {
         $user = $this->user;
         $nameUser = $user->first_name . $user->last_name;

         return Notification::query()->create([
            'user_id' => @$jobPosting->store->owner->id,
            'notice_type_id' => Notification::TYPE_INTERVIEW_APPLY,
            'noti_object_ids' => [
                'store_id' => $application->store_id,
                'application_id' => $application->id,
            ],
            'title' => trans('notification.N001.title', ['user_name' => $nameUser]),
            'content' => trans('notification.N001.content', ['user_name' => $nameUser]),
        ]);
    }

    /**
     * Store user chat
     *
     * @param $application
     * @param $jobPosting
     * @return bool|Builder|Model
     */
    public function storeChat($application, $jobPosting)
    {
        $now = now();
        $fromUser = [
            'user_id' => $application->user_id,
            'store_id' => $application->store_id,
            'is_from_user' => Chat::FROM_USER['TRUE'],
            'be_readed' => Chat::UNREAD,
            'is_apply_message' => Chat::APPLY_MESSAGE['FROM_USER'],
            'content' => trans('chat.C001_from_user', [
                'name' => $this->user->full_name,
                'job_name' => $jobPosting->name
            ]),
            'created_at' => $now
        ];
        $fromRec = [
            'user_id' => $application->user_id,
            'store_id' => $application->store_id,
            'is_from_user' => Chat::FROM_USER['FALSE'],
            'be_readed' => Chat::UNREAD,
            'is_apply_message' => Chat::APPLY_MESSAGE['FROM_REC'],
            'content' => trans('chat.C002_from_rec', [
                'name' => $jobPosting->store->owner->full_name
            ]),
            'created_at' => $now
        ];

        Chat::insert([
            $fromUser,
            $fromRec
        ]);

        return true;
    }

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
                'application_id' => $application->id,
                'job_type_id' => $userWorkHistory->job_type_id,
                'work_type_id' => $userWorkHistory->work_type_id,
                'store_name' => $userWorkHistory->store_name,
                'company_name' => $userWorkHistory->company_name,
                'period_start' => $userWorkHistory->period_start,
                'period_end' => $userWorkHistory->period_end,
                'position_office_ids' => json_encode($userWorkHistory->position_office_ids),
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
                'application_id' => $application->id,
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
                'application_id' => $application->id,
                'name' => $userLicensesQualificationHistory->name,
                'new_issuance_date' => $userLicensesQualificationHistory->new_issuance_date,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return ApplicationUserLicensesQualification::query()->insert($data);
    }

    /**
     * @param $application
     * @return bool
     */
    public function storeApplicationUser($application)
    {
        $user = User::query()->with(['avatarBanner', 'avatarDetails'])->where('id', $application->user_id)->first();

        $dataApplicationUser = [
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'role_id' => $user->role_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'furi_first_name' => $user->furi_first_name,
            'furi_last_name' => $user->furi_last_name,
            'alias_name' => $user->alias_name,
            'birthday' => $user->birthday,
            'age' => $user->age,
            'gender_id' => $user->gender_id,
            'tel' => $user->tel,
            'email' => $user->email,
            'line' => $user->line,
            'facebook' => $user->facebook,
            'instagram' => $user->instagram,
            'twitter' => $user->twitter,
            'postal_code' => $user->postal_code,
            'province_id' => $user->province_id,
            'province_city_id' => $user->province_city_id,
            'building' => $user->building,
            'address' => $user->address,
            'favorite_skill' => $user->favorite_skill,
            'experience_knowledge' => $user->experience_knowledge,
            'self_pr' => $user->self_pr,
            'motivation' => $user->motivation,
            'noteworthy' => $user->noteworthy,
            'skills' => $user->skills,
            'is_public_avatar' => !!$user->is_public_avatar,
        ];

        $applicationUser = ApplicationUser::query()->create($dataApplicationUser);

        $images = [];

        foreach ($user->avatarDetails as $avatar) {
            $images[] = [
                'imageable_id' => $applicationUser->id,
                'imageable_type' => get_class($applicationUser),
                'url' => $avatar->url,
                'thumb' => $avatar->thumb,
                'type' => $avatar->type,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($user->avatarBanner) {
            $images = array_merge([[
                'imageable_id' => $applicationUser->id,
                'imageable_type' => get_class($applicationUser),
                'url' => $user->avatarBanner->url,
                'thumb' => $user->avatarBanner->thumb,
                'type' => $user->avatarBanner->type,
                'created_at' => now(),
                'updated_at' => now(),
            ]], $images);
        }

        Image::query()->insert($images);

        return true;
    }
}
