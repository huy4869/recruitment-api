<?php

namespace App\Services\Recruiter\User;

use App\Exceptions\InputException;
use App\Helpers\JobHelper;
use App\Helpers\UserHelper;
use App\Models\FavoriteUser;
use App\Models\Notification;
use App\Models\User;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\DB;

class UserService extends Service
{
    /**
     * @return array
     */
    public function getNewUsers()
    {
        $recruiter = $this->user;
        $userNewList = User::query()->roleUser()
            ->with([
                'avatarBanner',
                'province',
                'province.provinceDistrict',
                'desiredConditionUser',
                'desiredConditionUser.salaryType',
                'desiredConditionUser.province',
                'desiredConditionUser.province.provinceDistrict',
            ])
            ->orderBy('created_at', 'desc')
            ->take(config('paginate.user.new_amount'))
            ->get();

        $recruiterFavoriteUser = $recruiter->favoriteUsers()->pluck('favorite_user_id')->toArray();

        return self::getUserInfoForListUser($recruiterFavoriteUser, $userNewList);
    }

    /**
     * @return array
     */
    public function getSuggestUsers()
    {
        $recruiter = $this->user;
        $jobOwnedIds = $recruiter->jobsOwned()->pluck('job_postings.id')->toArray();

        $userSuggestList = User::query()->roleUser()
            ->select('users.*', DB::raw('sum(suitability_point) as point'))
            ->leftJoin('user_job_desired_matches', 'users.id', '=', 'user_id')
            ->whereIn('job_id', $jobOwnedIds)
            ->groupBy('user_id')
            ->orderBy('point', 'DESC')
            ->orderBy('last_login_at', 'DESC')
            ->orderBy('users.created_at', 'DESC')
            ->take(config('paginate.user.suggest_amount'))
            ->get();

        $recruiterFavoriteUser = $recruiter->favoriteUsers()->pluck('favorite_user_id')->toArray();

        return self::getUserInfoForListUser($recruiterFavoriteUser, $userSuggestList);
    }

    /**
     * @param $data
     * @return bool
     * @throws InputException
     * @throws Exception
     */
    public function favoriteUser($data)
    {
        $user = User::query()->where('id', $data['user_id'])->roleUser()->first();

        if ($user) {
            $recruiter = $this->user;
            $favoriteUser = $recruiter->favoriteUsers()->where('favorite_user_id', $user->id)->first();

            if ($favoriteUser) {
                return false;
            }

            try {
                DB::beginTransaction();

                FavoriteUser::create([
                    'user_id' => $recruiter->id,
                    'favorite_user_id' => $user->id
                ]);

                $recruiterJobIds = $recruiter->jobsOwned()->pluck('job_postings.id')->toArray();
                $userFavoriteJobs = $user->favoriteJobs()
                    ->whereIn('job_posting_id', $recruiterJobIds)
                    ->with([
                        'jobPosting',
                        'jobPosting.store'
                    ])
                    ->get();

                $userNotifyData = [];

                foreach ($userFavoriteJobs as $favoriteJob) {
                    $userNotifyData[] = ([
                        'user_id' => $user->id,
                        'notice_type_id' => Notification::TYPE_MATCHING_FAVORITE,
                        'noti_object_ids' => json_encode([
                            'user_id' => $recruiter->id,
                            'job_id' => $favoriteJob->jobPosting->id,
                            'store_id' => $favoriteJob->jobPosting->store->id,
                        ]),
                        'title' => trans('notification.N010.title', [
                            'store_name' => $favoriteJob->jobPosting->store->name,
                        ]),
                        'content' => trans('notification.N010.content', [
                            'store_name' => $favoriteJob->jobPosting->store->name,
                        ]),
                        'created_at' => now(),
                    ]);
                }

                if (count($userNotifyData)) {
                    Notification::insert($userNotifyData);
                }

                DB::commit();
                return true;
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        throw new InputException(trans('response.invalid'));
    }

    /**
     * @param $data
     * @return bool
     * @throws InputException
     */
    public function unfavoriteUser($data)
    {
        $user = User::query()->where('id', $data['user_id'])->roleUser()->first();

        if ($user) {
            $recruiter = $this->user;

            return $recruiter->favoriteUsers()->where('favorite_user_id', $user->id)->delete();
        }

        throw new InputException(trans('response.invalid'));
    }

    /**
     * Get user info for list user
     *
     * @param $recruiterFavoriteUser
     * @param $userList
     * @return array
     */
    public static function getUserInfoForListUser($recruiterFavoriteUser, $userList)
    {
        $jobMasterData = UserHelper::getJobMasterData();
        $userArr = [];

        foreach ($userList as $user) {
            $userDesiredCondition = $user->desiredConditionUser;
            if (isset($userDesiredCondition->job_type_ids)) {
                $user->job_types = JobHelper::getTypeName(
                    $userDesiredCondition->job_type_ids,
                    $jobMasterData['masterJobTypes']
                );
            }
            if (isset($userDesiredCondition->job_experience_ids)) {
                $user->job_experiences = JobHelper::getTypeName(
                    $userDesiredCondition->job_experience_ids,
                    $jobMasterData['masterJobExperiences']
                );
            }

            if (isset($userDesiredCondition->job_feature_ids)) {
                $user->job_features = UserHelper::getFeature(
                    $userDesiredCondition->job_feature_ids,
                    $jobMasterData['masterJobFeatures']
                );
            }

            if (isset($userDesiredCondition->work_type_ids)) {
                $user->work_types = JobHelper::getTypeName(
                    $userDesiredCondition->work_type_ids,
                    $jobMasterData['masterWorkTypes']
                );
            }

            if (isset($userDesiredCondition->province_ids)) {
                $user->provinces = UserHelper::getListProvinceNames(
                    $userDesiredCondition->province_ids,
                    $jobMasterData['masterProvinces']
                );
            }

            $user->favorite = !!in_array($user->id, $recruiterFavoriteUser);

            $userArr[$user->id] = $user;
        }//end foreach

        return $userArr;
    }
}
