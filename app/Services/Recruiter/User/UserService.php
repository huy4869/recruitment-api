<?php

namespace App\Services\Recruiter\User;

use App\Helpers\JobHelper;
use App\Helpers\UserHelper;
use App\Models\User;
use App\Services\Service;
use Illuminate\Support\Collection;
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

        $recruiterFavoriteUser = $recruiter->favoriteUser->favorite_ids;

        return self::getUserInfoForListUser($recruiterFavoriteUser, $userNewList);
    }

    /**
     * @return Collection
     */
    public function getSuggestUsers()
    {
        $recruiter = $this->user;
        $queryJob = sprintf('job_id IN (
                SELECT id
                FROM job_postings
                WHERE store_id IN (
                    SELECT id
                    FROM stores
                    WHERE user_id = %u
                )
            )', $recruiter->id);

        $userIds = DB::table('user_job_desired_matches')
            ->select('user_id', 'users.last_login_at', DB::raw('sum(suitability_point) as point'))
            ->join('users', 'user_id', '=', 'users.id')
            ->whereRaw($queryJob)
            ->groupBy('user_id')
            ->orderByRaw('point DESC')
            ->orderByRaw('last_login_at DESC')
            ->take(config('paginate.user.suggest_amount'))
            ->pluck('user_id')
            ->toArray();

        $userSuggestList = User::query()->roleUser()
            ->whereIn('id', $userIds)
            ->with([
                'avatarBanner',
                'province',
                'province.provinceDistrict',
                'desiredConditionUser',
                'desiredConditionUser.salaryType',
                'desiredConditionUser.province',
                'desiredConditionUser.province.provinceDistrict',
            ])
            ->get();

        $recruiterFavoriteUser = $recruiter->favoriteUser->favorite_ids;
        $userSuggestListFullInfo = self::getUserInfoForListUser($recruiterFavoriteUser, $userSuggestList);

        return collect($userIds)->map(function ($id) use ($userSuggestListFullInfo) {
            return $userSuggestListFullInfo[$id];
        });
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
            $user->job_types = JobHelper::getTypeName(
                $userDesiredCondition->job_type_ids,
                $jobMasterData['masterJobTypes']
            );
            $user->job_experiences = JobHelper::getTypeName(
                $userDesiredCondition->job_experience_ids,
                $jobMasterData['masterJobExperiences']
            );
            $user->job_features = JobHelper::getFeatureCategoryName(
                $userDesiredCondition->job_feature_ids,
                $jobMasterData['masterJobFeatures']
            );
            $user->work_types = JobHelper::getTypeName(
                $userDesiredCondition->work_type_ids,
                $jobMasterData['masterWorkTypes']
            );
            $user->favorite = !!in_array($user->id, $recruiterFavoriteUser);

            $userArr[$user->id] = $user;
        }//end foreach

        return $userArr;
    }
}
