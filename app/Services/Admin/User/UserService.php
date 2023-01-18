<?php

namespace App\Services\Admin\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Helpers\JobHelper;
use App\Helpers\UrlHelper;
use App\Helpers\UserHelper;
use App\Jobs\Admin\User\JobDestroy;
use App\Jobs\Admin\User\JobStore;
use App\Jobs\Admin\User\JobUpdate;
use App\Jobs\User\JobVerifyRegister;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\MPositionOffice;
use App\Models\MProvince;
use App\Models\MRole;
use App\Models\Notification;
use App\Models\Store;
use App\Models\StoreOffTime;
use App\Models\User;
use App\Models\UserJobDesiredMatch;
use App\Services\Common\FileService;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService extends Service
{
    /**
     * Detail user
     *
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($id)
    {
        $user = User::query()->where('id', $id)->with('stores')->withTrashed()->first();

        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * @param $data
     * @return Builder|Model
     * @throws Exception
     */
    public function store($data)
    {
        $admin = $this->user;

        if (
            $admin->role_id == User::ROLE_SUB_ADMIN
            && $data['role_id'] == User::ROLE_SUB_ADMIN
        ) {
            throw new InputException(trans('response.invalid'));
        }

        try {
            DB::beginTransaction();
            $newUser = User::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'furi_first_name' => $data['furi_first_name'],
                'furi_last_name' => $data['furi_last_name'],
                'email' => Str::lower($data['email']),
                'password' => Hash::make($data['password']),
                'role_id' => $data['role_id'],
                'verify_token' => Str::random(config('password_reset.token.length_verify')),
            ]);

            if (!$newUser) {
                throw new InputException(__('auth.register_fail'));
            }

            $role = MRole::where('id', $data['role_id'])->first();
            dispatch(new JobStore($data, $role))->onQueue(config('queue.email_queue'));

            if ($newUser->role_id == User::ROLE_USER || $newUser->role_id == User::ROLE_RECRUITER) {
                $this->sendMailVerifyRegister($newUser);
            }

            DB::commit();
            return $newUser;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception->getMessage());
        }
    }

    /**
     * Send mail verify register
     *
     * @param $newUser
     */
    public function sendMailVerifyRegister($newUser)
    {
        $token = Crypt::encryptString($newUser->email . '&' . $newUser->verify_token);
        $url = UrlHelper::verifyRegisterLink($token, $newUser);

        $infoSendMail = [
            'email' => $newUser->email,
            'subject' => trans('mail.subject.verify_account'),
            'url' => $url,
        ];

        dispatch(new JobVerifyRegister($infoSendMail))->onQueue(config('queue.email_queue'));
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function update($id, $data)
    {
        $admin = $this->user;
        $user = User::query()->where('id', $id)->with('role')->first();

        if (!$user || (
                $admin->role_id == User::ROLE_SUB_ADMIN
                && $user->role_id == User::ROLE_SUB_ADMIN
            )) {
            throw new InputException(trans('response.invalid'));
        }

        try {
            DB::beginTransaction();

            $oldUserPassword = $user->password;
            $newUserPassword = $data['password'];
            $data['password'] = Hash::make($data['password']);
            $user->update($data);

            if (!Hash::check($newUserPassword, $oldUserPassword)) {
                dispatch(new JobUpdate([
                    'user' => $user,
                    'update_data' => $data,
                    'new_password' => $newUserPassword
                ]))
                    ->onQueue(config('queue.email_queue'));
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }

    /**
     * @return Builder[]
     */
    public static function getUserRoleIdCanModify($roleId)
    {
        $condition = [User::ROLE_ADMIN];

        if ($roleId == User::ROLE_SUB_ADMIN) {
            $condition[] = User::ROLE_SUB_ADMIN;
        }

        return MRole::query()->whereNot('id', $condition)
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param $id
     * @return bool
     * @throws InputException
     * @throws Exception
     */
    public function destroy($id)
    {
        $admin = $this->user;

        $user = User::query()->where('id', $id)
            ->with([
                'stores',
                'stores.jobs',
                'stores.jobs.applications',
                'applications.jobPosting',
                'applications.jobPosting.store.owner',
            ])->first();

        if (!$user || (
                $admin->role_id == User::ROLE_SUB_ADMIN
                && $user->role_id == User::ROLE_SUB_ADMIN
            )) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            switch ($user->role_id) {
                case User::ROLE_SUB_ADMIN:
                    $result = self::destroySubAdmin($user);
                    break;
                case User::ROLE_RECRUITER:
                    $result = self::destroyRecruiter($admin, $user);
                    break;
                case User::ROLE_USER:
                    $result = self::destroyUser($admin, $user);
                    break;
                default:
                    return false;
            }

            dispatch(new JobDestroy($user))->onQueue(config('queue.email_queue'));

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param $admin
     * @param $user
     * @return bool
     * @throws Exception
     */
    public function destroyUser($admin, $user)
    {
        $userNotifyData = [];

        foreach ($user->applications as $application) {
            if (!$application->jobPosting) {
                continue;
            }

            $recruiter = $application->jobPosting->store->owner;

            $userNotifyData[] = [
                'user_id' => $recruiter->id,
                'notice_type_id' => Notification::TYPE_DELETE_USER,
                'noti_object_ids' => json_encode([
                    'job_posting_id' => $application->job_posting_id,
                    'application_id' => $application->id,
                    'user_id' => $admin->id,
                ]),
                'title' => trans('notification.N014.title'),
                'content' => trans('notification.N014.content', [
                    'user_name' => sprintf('%s %s', $user->first_name, $user->last_name),
                    'job_title' => $application->jobPosting->name,
                ]),
                'created_at' => now(),
            ];
        }

        $user->applicationUserLearningHistories()?->delete();
        $user->applicationUserLicensesQualifications()?->delete();
        $user->applicationUserWorkHistories()?->delete();
        $user->applications()?->notAccept()?->delete();
        $user->contacts()?->delete();
        $user->desiredConditionUser()?->delete();
        $user->favoriteJobs()?->delete();
        $user->feedbacks()?->delete();
        $user->images()?->delete();
        $user->notifications()?->delete();
        $user->searchJobs()?->delete();
        $user->userJobDesiredMatches()?->delete();
        $user->userLearningHistories()?->delete();
        $user->userLicensesQualifications()?->delete();
        $user->userWordHistories()?->delete();
        $user->delete();

        Notification::insert($userNotifyData);

        return true;
    }

    /**
     * @param $admin
     * @param $recruiter
     * @return bool
     * @throws Exception
     */
    public function destroyRecruiter($admin, $recruiter)
    {
        $userNotifyData = [];
        $storeIds = [];

        foreach ($recruiter->stores as $store) {
            $storeIds[] = $store->id;
            foreach ($store->jobs as $job) {
                foreach ($job->applications as $application) {
                    $userNotifyData[] = [
                        'user_id' => $application->user_id,
                        'notice_type_id' => Notification::TYPE_DELETE_RECRUITER,
                        'noti_object_ids' => json_encode([
                            'job_posting_id' => $job->id,
                            'application_id' => $application->id,
                            'user_id' => $admin->id,
                        ]),
                        'title' => trans('notification.N013.title'),
                        'content' => trans('notification.N013.content', [
                            'recruiter_name' => sprintf('%s %s', $recruiter->first_name, $recruiter->last_name),
                            'job_title' => $application->jobPosting->name,
                        ]),
                        'created_at' => now(),
                    ];
                }
            }
        }

        StoreOffTime::query()->whereIn('store_id', $storeIds)->delete();
        $recruiter->favoriteUsers()?->delete();
        $recruiter->images()?->delete();
        $recruiter->contacts()?->delete();
        $recruiter->notifications()?->delete();
        $recruiter->applicationOwned()?->update([
            'interview_status_id' => MInterviewStatus::STATUS_REJECTED,
        ]);
        $jobOwnedIds = $recruiter->jobsOwned()->pluck('job_postings.id')->toArray();
        UserJobDesiredMatch::query()->whereIn('job_id', $jobOwnedIds)->delete();
        $recruiter->jobsOwned()?->delete();
        $recruiter->stores()?->delete();
        $recruiter->delete();

        Notification::insert($userNotifyData);

        return true;
    }

    /**
     * @param $subAdmin
     * @return mixed
     */
    public function destroySubAdmin($subAdmin)
    {
        return $subAdmin->delete();
    }

    public function detailInfoUser($user_id)
    {
        $user = User::query()
            ->with([
                'userLearningHistories' => function ($query) {
                    $query->orderByRaw('enrollment_period_start ASC, enrollment_period_end ASC');
                },
                'userLicensesQualifications' => function ($query) {
                    $query->orderByRaw('new_issuance_date ASC, created_at ASC');
                },
                'userWordHistories' => function ($query) {
                    $query->orderByRaw('period_end is not null, period_start DESC, period_end DESC');
                },
                'avatarBanner',
                'avatarDetails'
            ])
            ->where('id', $user_id)
            ->roleUser()
            ->withTrashed()
            ->first();

        if ($user) {
            $masterData = UserHelper::getMasterDataWithUser();

            return self::addFormatUserProfileJsonData($user, $masterData);
        }

        return null;
    }

    public static function addFormatUserProfileJsonData($user, $masterData)
    {
        $positionOffice = MPositionOffice::where('is_default', MPositionOffice::IS_DEFAULT)->orWhere('created_by', $user->id)->get();
        $userWorkHistories = [];
        foreach ($user->userWordHistories as $workHistory) {
            $userWorkHistories[] = [
                'id' => $workHistory->id,
                'store_name' => $workHistory->store_name,
                'company_name' => $workHistory->company_name,
                'business_content' => $workHistory->business_content,
                'experience_accumulation' => $workHistory->experience_accumulation,
                'date_time' => [
                    'work_time' => DateTimeHelper::formatDateStartEnd($workHistory->period_start, $workHistory->period_end),
                    'period_start' => [
                        'month' => substr($workHistory->period_start, 4),
                        'year' => substr($workHistory->period_start, 0, 4),
                    ],
                    'period_end' => [
                        'month' => substr($workHistory->period_end, 4),
                        'year' => substr($workHistory->period_end, 0, 4),
                    ]
                ],
                'job_type' => @$workHistory->jobType->name,
                'positionOffices' => @JobHelper::getTypeName($workHistory->position_office_ids, $masterData['masterPositionOffice']),
                'work_type' => @$workHistory->workType->name,
            ];
        }//end foreach

        $learningHistories = [];
        foreach ($user->userLearningHistories as $learningHistory) {
            $learningHistories[] = [
                'id' => $learningHistory->id,
                'school_name' => $learningHistory->school_name,
                'date_time' => [
                    'time_start_end' => sprintf(
                        '%s～%s%s',
                        DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_start),
                        DateTimeHelper::formatMonthYear($learningHistory->enrollment_period_end),
                        @$learningHistory->learningStatus->name ? trans('common.learning_status_name', ['status_name' => $learningHistory->learningStatus->name]) : null,
                    ),
                    'enrollment_period_start' => [
                        'month' => substr($learningHistory->enrollment_period_start, 4),
                        'year' => substr($learningHistory->enrollment_period_start, 0, 4),
                    ],
                    'enrollment_period_end' => [
                        'month' => substr($learningHistory->enrollment_period_end, 4),
                        'year' => substr($learningHistory->enrollment_period_end, 0, 4),
                    ]
                ],
            ];
        }//end foreach

        $licensesQualifications = [];
        foreach ($user->userLicensesQualifications as $userLicensesQualification) {
            $licensesQualifications[] = [
                'id' => $userLicensesQualification->id,
                'name' => $userLicensesQualification->name,
                'date_time' => [
                    'new_issuance_date' => DateTimeHelper::formatMonthYear($userLicensesQualification->new_issuance_date),
                    'format_issuance_date' => [
                        'month' => substr($userLicensesQualification->new_issuance_date, 4),
                        'year' => substr($userLicensesQualification->new_issuance_date, 0, 4),
                    ]
                ]
            ];
        }

        return array_merge($user->toArray(), [
            'avatar_banner' => FileHelper::getFullUrl($user->avatarBanner->url ?? null),
            'position_offices' => $positionOffice,
            'avatar_details' => $user->avatarDetails ?: null,
            'province' => @$user->province->name,
            'province_city' => @$user->provinceCity->name,
            'gender' => $user->gender->name ?? null,
            'gender_id' => $user->gender->id ?? null,
            'user_work_histories' => $userWorkHistories,
            'favorite_skill' => $user->favorite_skill,
            'experience_knowledge' => $user->experience_knowledge,
            'self_pr' => $user->self_pr,
            'user_learning_histories' => $learningHistories,
            'user_licenses_qualifications' => $licensesQualifications,
            'motivation' => $user->motivation,
        ]);
    }

    public function updateUser($data, $id)
    {
        $user = User::query()->where('id', $id)->roleUser()->first();

        if (!$user) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            $user->update($data);

            if (isset($data['images'])) {
                FileService::getInstance()->updateImageable($user, $this->makeSaveDataImage($data));
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception->getMessage());
        }
    }

    /**
     * Make Save data
     *
     * @param $data
     * @return array
     */
    private function makeSaveData($data)
    {
        if (isset($data['is_public_avatar'])) {
            return [
                'is_public_avatar' => $data['is_public_avatar']
            ];
        }

        $result = [];

        $attrs = [
            'first_name',
            'last_name',
            'alias_name',
            'furi_first_name',
            'furi_last_name',
            'birthday',
            'gender_id',
            'tel',
            'line',
            'facebook',
            'instagram',
            'twitter',
            'postal_code',
            'province_id',
            'province_city_id',
            'address',
            'building',
            'is_public_avatar',
        ];

        foreach ($attrs as $attr) {
            $result[$attr] = @$data[$attr];
        }

        return $result;
    }

    /**
     * Make Save data images
     *
     * @param $data
     * @return array
     */
    private function makeSaveDataImage($data)
    {
        $dataUrl = [];
        foreach ($data['images'] as $image) {
            $dataUrl[] = FileHelper::fullPathNotDomain($image['url']);
        }

        return array_merge([FileHelper::fullPathNotDomain($data['avatar'])], $dataUrl);
    }

    public function updatePr($data, $userId)
    {
        return User::query()
            ->roleUser()
            ->where('id', $userId)
            ->update($data);
    }

    public function updateMotivation($userId, $data)
    {
        return User::query()->where('id', $userId)->update($data);
    }

    /**
     * Get user info for list user
     *
     * @param $userList
     * @return array
     */
    public static function appendMasterDataForUser($userList)
    {
        $jobMasterData = UserHelper::getJobMasterData();
        $provinces = MProvince::query()->get();
        $userArr = [];

        foreach ($userList as $user) {
            $userDesiredCondition = $user->desiredConditionUser;
            $user->job_types = JobHelper::getTypeName(
                @$userDesiredCondition->job_type_ids,
                $jobMasterData['masterJobTypes']
            );
            $user->job_experiences = JobHelper::getTypeName(
                @$userDesiredCondition->job_experience_ids,
                $jobMasterData['masterJobExperiences']
            );
            $user->job_features = JobHelper::getTypeName(
                @$userDesiredCondition->job_feature_ids,
                $jobMasterData['masterJobFeatures']
            );
            $user->work_types = JobHelper::getTypeName(
                @$userDesiredCondition->work_type_ids,
                $jobMasterData['masterWorkTypes']
            );

            $user->province_name = UserHelper::getProvinceName($provinces, @$userDesiredCondition->province_ids);
            $userArr[$user->id] = $user;
        }//end foreach

        return $userArr;
    }

    public function getAllOwner()
    {
        return User::query()->roleRecruiter()
            ->get()
            ->map(function ($query) {
                return [
                    'id'    => $query->id,
                    'name'  => $query->full_name
                ];
            });
    }

    public function getInfoUser($userId)
    {
        $user = User::query()->where('id', $userId)->roleUser()->first();

        if ($user) {
            return $user;
        }

        throw new InputException(trans('response.not_found'));
    }

    public function getAvailableActionRoles()
    {
        $admin = $this->user;
        $notCondition = [
            User::ROLE_ADMIN
        ];

        if ($admin->role_id == User::ROLE_SUB_ADMIN) {
            $notCondition[] = User::ROLE_SUB_ADMIN;
        }

        return MRole::query()->whereNotIn('id', $notCondition)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($query) {
                return [
                    'id'    => $query->id,
                    'name'  => $query->name
                ];
            });
    }
}
