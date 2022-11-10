<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\UserLearningHistory;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LearningHistoryService extends Service
{
    /**
     * List user learning history
     *
     * @return Builder[]|Collection
     */
    public function list()
    {
        return UserLearningHistory::query()
            ->with('learningStatus')
            ->where('user_id', $this->user->id)
            ->orderBy('enrollment_period_start', 'ASC')
            ->orderBy('enrollment_period_end', 'ASC')
            ->get();
    }

    /**
     * User store learning history
     *
     * @param $data
     * @return Builder|Model
     */
    public function store($data)
    {
        return UserLearningHistory::query()->create($this->makeSaveData($data));
    }

    /**
     * Detail user work history
     *
     * @param $idLearningHistory
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($idLearningHistory)
    {
        return $this->checkDataObject($idLearningHistory);
    }

    /**
     * Update user work history
     *
     * @param $idLearningHistory
     * @param $data
     * @return bool|int
     * @throws InputException
     */
    public function update($idLearningHistory, $data)
    {
        return $this->checkDataObject($idLearningHistory)->update($this->makeSaveData($data));
    }

    /**
     * Delete user work history
     *
     * @param $idLearningHistory
     * @return bool|mixed|null
     * @throws InputException
     */
    public function delete($idLearningHistory)
    {
        return $this->checkDataObject($idLearningHistory)->delete();
    }

    /**
     * Check object user learning history
     *
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    private function checkDataObject($id)
    {
        $userLearningHistory = UserLearningHistory::query()
            ->where('user_id', '=', $this->user->id)
            ->where('id', $id)
            ->first();

        if ($userLearningHistory) {
            return $userLearningHistory;
        }

        throw new InputException(trans('response.not_found'));
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
            'learning_status_id' => $data['learning_status_id'],
            'school_name' => $data['school_name'],
            'enrollment_period_start' => str_replace('/', '', $data['enrollment_period_start']),
            'enrollment_period_end' => str_replace('/', '', $data['enrollment_period_end']),
        ];
    }
}
