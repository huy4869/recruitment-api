<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\DesiredConditionUser;
use App\Models\MJobExperience;
use App\Models\MJobFeature;
use App\Models\MJobType;
use App\Models\MProvince;
use App\Models\MWorkType;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DesiredConditionService extends Service
{
    /**
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getList($relations = [])
    {
        if (count($relations)) {
            return DesiredConditionUser::query()->with($relations)->get();
        }

        return DesiredConditionUser::query()->get();
    }

    /**
     * Detail user desired condition
     *
     * @throws InputException
     */
    public function detail()
    {
        $userWorkHistory = DesiredConditionUser::query()
            ->with(['province', 'salaryType'])
            ->where('user_id', '=', $this->user->id)
            ->first();

        if ($userWorkHistory) {
            $userWorkHistory['list_province'] = $this->getDataStringObject($userWorkHistory['province_ids'] ?? [], MProvince::query());
            $userWorkHistory['work_type_string'] = $this->getDataStringObject($userWorkHistory['work_type_ids'] ?? [], MWorkType::query());
            $userWorkHistory['job_type_string'] = $this->getDataStringObject($userWorkHistory['job_type_ids'] ?? [], MJobType::query());
            $userWorkHistory['job_experience_strings'] = $this->getDataStringObject($userWorkHistory['job_experience_ids'] ?? [], MJobExperience::query());
            $userWorkHistory['job_feature_string'] = $this->getDataStringObject($userWorkHistory['job_feature_ids'] ?? [], MJobFeature::query());

            return $userWorkHistory;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * String name object
     *
     * @param $data
     * @param $object
     * @return mixed|string
     */
    public function getDataStringObject($data, $object)
    {
        $dataObject = $object->whereIn('id', $data)->get()->pluck('name')->toArray();

        return count($dataObject) == 1 ? $dataObject[0] : implode('、', $dataObject);
    }

    /**
     * Get provinceIds
     *
     * @return array
     */
    public static function province()
    {
        return MProvince::query()->get()->pluck('id')->toArray();
    }

    /**
     * Store or update user desired condition
     *
     * @param $data
     * @return Builder|Model
     */
    public function storeOrUpdate($data)
    {
        return DesiredConditionUser::query()->updateOrCreate(
            [
                'user_id' => $this->user->id,
            ],
            $this->makeSaveData($data)
        );
    }

    /**
     * Make Save data
     *
     * @param $data
     * @return array
     */
    private function makeSaveData($data)
    {
        return [
            'user_id' => $this->user->id,
            'province_ids' => $data['province_ids'],
            'work_type_ids' => $data['work_type_ids'],
            'age' => $data['age_id'],
            'salary_type_id' => $data['salary_type_id'],
            'salary_min' => $data['salary_min'],
            'salary_max' => $data['salary_max'],
            'job_type_ids' => $data['job_type_ids'],
            'job_experience_ids' => $data['job_experience_ids'],
            'job_feature_ids' => $data['job_feature_ids'],
            'working_days' => $data['working_days'],
            'start_working_time' => $data['start_working_time'],
            'end_working_time' => $data['end_working_time'],
        ];
    }
}
