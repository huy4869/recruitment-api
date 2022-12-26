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
use Illuminate\Support\Facades\Session;

class UserService extends Service
{
    const APP_MODE = 1;
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
                'favoriteJobs'
            ])
            ->orderBy('created_at', 'desc')
            ->take(config('paginate.user.new_amount'))
            ->get();

        return self::getUserInfoForListUser($recruiter, $userNewList);
    }

    public function getAppNewUser($ids = [], $currentId = null)
    {
        $relation = [
            'avatarBanner',
            'province',
            'province.provinceDistrict',
            'desiredConditionUser',
            'desiredConditionUser.salaryType',
            'desiredConditionUser.province',
            'desiredConditionUser.province.provinceDistrict',
            'favoriteJobs',
            'userLicensesQualifications',
        ];

        if ($currentId) {
            $user = User::query()->roleUser()
                ->with($relation)
                ->where('id', $currentId)
                ->orderBy('created_at', 'desc')
                ->first();
        } else {
            $user = User::query()->roleUser()
                ->with($relation)
                ->whereNotIn('id', $ids)
                ->orderBy('created_at', 'desc')
                ->first();

            if (
                is_null($user) &&
                is_array(Session::get('new_watched_ids')) &&
                count(Session::get('new_watched_ids'))
            ) {
                Session::forget('new_watched_ids');
                $user = User::query()->roleUser()
                    ->with($relation)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        return self::getUserInfoForListUser($this->user, [$user]);
    }

    /**
     * @return array
     */
    public function getSuggestUsers()
    {
        $recruiter = $this->user;
        $jobOwnedIds = $recruiter->jobsOwned()->pluck('job_postings.id')->toArray();

        $userSuggestList = User::query()->roleUser()
            ->select('users.*', 'user_id', DB::raw('sum(suitability_point) as point'))
            ->leftJoin('user_job_desired_matches', 'users.id', '=', 'user_id')
            ->whereIn('job_id', $jobOwnedIds)
            ->groupBy('user_id')
            ->orderBy('point', 'DESC')
            ->orderBy('last_login_at', 'DESC')
            ->orderBy('users.created_at', 'DESC')
            ->take(config('paginate.user.suggest_amount'))
            ->get();

        return self::getUserInfoForListUser($recruiter, $userSuggestList);
    }

    /**
     * @return array
     */
    public function getAppSuggestUsers($ids = [], $currentId = null)
    {
        $recruiter = $this->user;
        $jobOwnedIds = $recruiter->jobsOwned()->pluck('job_postings.id')->toArray();

        $query = User::query()->roleUser()
            ->select('users.*', 'user_job_desired_matches.user_id', DB::raw('sum(suitability_point) as point'))
            ->leftJoin('user_job_desired_matches', 'users.id', '=', 'user_job_desired_matches.user_id')
            ->leftJoin('user_licenses_qualifications', 'users.id', '=', 'user_job_desired_matches.user_id')
            ->whereIn('job_id', $jobOwnedIds);

        if ($currentId) {
            $query->where('users.id', $currentId);
        } else {
            $query->whereNotIn('users.id', $ids);
        }

        $userSuggest = $query
            ->groupBy('user_job_desired_matches.user_id')
            ->orderBy('point', 'DESC')
            ->orderBy('last_login_at', 'DESC')
            ->orderBy('users.created_at', 'DESC')
            ->first();

        if (
            !$currentId &&
            is_null($userSuggest) &&
            is_array(Session::get('suggest_watched_ids')) &&
            count(Session::get('suggest_watched_ids'))
        ) {
            Session::forget('suggest_watched_ids');
            $userSuggest = User::query()->roleUser()
                ->select('users.*', 'user_job_desired_matches.user_id', DB::raw('sum(suitability_point) as point'))
                ->leftJoin('user_job_desired_matches', 'users.id', '=', 'user_job_desired_matches.user_id')
                ->leftJoin('user_licenses_qualifications', 'users.id', '=', 'user_job_desired_matches.user_id')
                ->whereIn('job_id', $jobOwnedIds)
                ->groupBy('user_job_desired_matches.user_id')
                ->orderBy('point', 'DESC')
                ->orderBy('last_login_at', 'DESC')
                ->orderBy('users.created_at', 'DESC')
                ->first();
        }

        return self::getUserInfoForListUser($recruiter, [$userSuggest]);
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
     * @param $recruiter
     * @param $userList
     * @return array
     */
    public static function getUserInfoForListUser($recruiter, $userList)
    {
        $recruiterFavoriteUser = $recruiter->favoriteUsers->pluck('favorite_user_id')->toArray();
        $recruiterJobIds = $recruiter->jobsOwned->pluck('job_postings.id')->toArray();
        $jobMasterData = UserHelper::getJobMasterData();
        $userArr = [];

        foreach ($userList as $user) {
            $userDesiredCondition = $user->desiredConditionUser;
            $userFavoriteJobs = $user->favoriteJobs->pluck('job_posting_id')->toArray();

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
                $user->job_features = JobHelper::getTypeName(
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
            $user->matching = array_intersect($recruiterJobIds, $userFavoriteJobs);

            $userArr[$user->id] = $user;
        }//end foreach

        return $userArr;
    }
}
