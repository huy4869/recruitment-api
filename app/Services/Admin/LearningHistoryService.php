<?php

namespace App\Services\Admin;

use App\Exceptions\InputException;
use App\Models\User;
use App\Models\UserLearningHistory;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LearningHistoryService extends Service
{
    /**
     * Create learning history
     *
     * @param $data
     * @param $userId
     * @return Builder|Model
     * @throws InputException
     */
    public function store($data, $userId)
    {
        $user = User::query()->roleUser()->where('id', $userId)->first();

        if (!$user) {
            throw new InputException(trans('validation.ERR.exist.user_not_exist'));
        }

        $dataCreate = [
            'user_id' => $userId,
            'learning_status_id' => $data['learning_status_id'],
            'school_name' => $data['school_name'],
            'enrollment_period_start' => str_replace('/', '', $data['enrollment_period_start']),
            'enrollment_period_end' => str_replace('/', '', $data['enrollment_period_end']),
        ];

        return UserLearningHistory::query()->create($dataCreate);
    }

    /**
     * Update learning history
     *
     * @param $data
     * @param $id
     * @param $userId
     * @return bool|int
     * @throws InputException
     */
    public function update($data, $id, $userId)
    {
        $learningHistory = UserLearningHistory::query()
            ->where([
                ['id', $id],
                ['user_id', $userId]
            ])
            ->first();

        if ($learningHistory) {
            $dataUpdate = [
                'learning_status_id' => $data['learning_status_id'],
                'school_name' => $data['school_name'],
                'enrollment_period_start' => str_replace('/', '', $data['enrollment_period_start']),
                'enrollment_period_end' => str_replace('/', '', $data['enrollment_period_end']),
            ];

            return $learningHistory->update($dataUpdate);
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * @param $id
     * @param $user_id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($id, $user_id)
    {
        $userLearningHistory = UserLearningHistory::query()
            ->where('user_id', '=', $user_id)
            ->where('id', $id)
            ->first();

        if ($userLearningHistory) {
            return $userLearningHistory;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * @param $id
     * @param $user_id
     * @return bool|mixed|null
     * @throws InputException
     */
    public function delete($id, $user_id)
    {
        $userLearningHistory = UserLearningHistory::query()
            ->where('user_id', '=', $user_id)
            ->where('id', $id)
            ->first();

        if ($userLearningHistory) {
            return $userLearningHistory->delete();
        }

        throw new InputException(trans('response.not_found'));
    }
}
