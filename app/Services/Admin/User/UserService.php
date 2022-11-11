<?php

namespace App\Services\Admin\User;

use App\Exceptions\InputException;
use App\Jobs\Admin\User\JobStore;
use App\Jobs\Admin\User\JobUpdate;
use App\Models\MRole;
use App\Models\Store;
use App\Models\Application;
use App\Models\Notification;
use App\Models\User;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        $user = User::query()->where('id', $id)->with('stores')->first();

        if (!$user) {
            throw new InputException(trans('response.not_found'));
        }

        return $user;
    }

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function store($data)
    {
        if ($data['role_id'] != User::ROLE_RECRUITER && isset($data['store_ids'])) {
            unset($data['store_id']);
        }

        try {
            DB::beginTransaction();

            $newUser = User::create($data);

            if ($data['role_id'] == User::ROLE_RECRUITER && isset($data['store_ids'])) {
                Store::query()->whereIn('id', $data['store_ids'])
                ->update([
                    'user_id' => $newUser->id,
                ]);
            }

            dispatch(new JobStore($data))->onQueue(config('queue.email_queue'));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
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
            throw new InputException(trans('response.not_found'));
        }

        if ($user->role_id != User::ROLE_RECRUITER && isset($data['store_ids'])) {
            unset($data['store_id']);
        }

        try {
            DB::beginTransaction();

            $oldUserPassword = $user->password;
            $newUserPassword = $data['password'];
            $data['password'] = Hash::make($data['password']);
            $user->update($data);

            if ($user->role_id == User::ROLE_RECRUITER && isset($data['store_ids'])) {
                Store::query()->whereIn('id', $data['store_ids'])
                ->update([
                    'user_id' => $user->id,
                ]);
            }

            if (!Hash::check($newUserPassword, $oldUserPassword)) {
                dispatch(new JobUpdate([
                    'user' => $user,
                    'update_data' => $data
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

        $user = User::query()->where('id', $id)->with([
            'stores',
            'stores.jobs',
            'stores.jobs.applications',
            'applications.jobPosting',
            'applications.jobPosting.store.owner',
        ])
        ->first();

        if (!$user || (
            $admin->role_id == User::ROLE_SUB_ADMIN
            && $user->role_id == User::ROLE_SUB_ADMIN
        )) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            $userNotifyData = [];

            switch ($user->role_id) {
                case User::ROLE_USER:
                    foreach ($user->applications as $application) {
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
                    break;

                case User::ROLE_RECRUITER:
                    foreach ($user->stores as $store) {
                        foreach ($store->jobs as $job) {
                            foreach ($job->applications as $application) {
                                $userNotifyData[] = [
                                    'user_id' => $application->user->id,
                                    'notice_type_id' => Notification::TYPE_DELETE_RECRUITER,
                                    'noti_object_ids' => json_encode([
                                        'job_posting_id' => $job->id,
                                        'application_id' => $application->id,
                                        'user_id' => $admin->id,
                                    ]),
                                    'title' => trans('notification.N013.title'),
                                    'content' => trans('notification.N013.content', [
                                        'recruiter_name' => sprintf('%s %s', $user->first_name, $user->last_name),
                                        'job_title' => $application->jobPosting->name,
                                    ]),
                                    'created_at' => now(),
                                ];
                            }
                        }
                    }
                    break;
            }

            Notification::insert($userNotifyData);

            $user->applicationUserLearningHistories()?->delete();
            $user->applicationUserLicensesQualifications()?->delete();
            $user->applicationUserWorkHistories()?->delete();

            foreach ($user->applicationUser as $item) {
                $item->images()?->delete();
            }

            $user->applications()?->update([
                'interview_status_id' => Application::STATUS_REJECTED,
            ]);

            $user->chats()?->delete();
            $user->contacts()?->delete();
            $user->desiredConditionUser()?->delete();
            $user->favoriteJobs()?->delete();
            $user->favoriteUser()?->delete();
            $user->feedbacks()?->delete();
            $user->images()?->delete();
            $user->notifications()?->delete();
            $user->recruiterOffTimes()?->delete();
            $user->searchJobs()?->delete();

            foreach ($user->stores as $store) {
                foreach ($store->jobs() as $job) {
                    $job->applications()->update([
                        'interview_status_id' => Application::STATUS_REJECTED,
                    ]);
                }

                $store->jobs()?->delete();
            }

            $user->stores()?->delete();
            $user->userJobDesiredMatches()?->delete();
            $user->userLearningHistories()?->delete();
            $user->userLicensesQualifications()?->delete();
            $user->userWordHistories()?->delete();
            $user->delete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }
}
