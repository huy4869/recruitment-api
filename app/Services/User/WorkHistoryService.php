<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\MJobType;
use App\Models\MPositionOffice;
use App\Models\MWorkType;
use App\Models\UserWorkHistory;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkHistoryService extends Service
{
    const JOB_TYPES = 'm_job_type';
    const WORK_TYPES = 'm_work_type';
    const POSITION_OFFICES = 'position_offices';

    /**
     * List user work history
     *
     * @return Builder[]|Collection
     */
    public function list()
    {
        $user = $this->user;
        $userWorkHistories = UserWorkHistory::query()
            ->with(['jobType', 'workType'])
            ->where('user_id', $user->id)
            ->orderBy('period_end', 'DESC')
            ->get()
            ->toArray();

        $positionOffices = MPositionOffice::all()->pluck('name', 'id')->toArray();

        foreach ($userWorkHistories as $key => $userWorkHistory) {
            $userWorkHistories[$key]['position_offices'] = $this->getArrayValueByKeys($positionOffices, $userWorkHistory['position_office_ids']);
        }

        return $userWorkHistories;
    }

    /**
     * @param $values
     * @param $keys
     * @return string
     */
    public function getArrayValueByKeys($values, $keys)
    {
        $arrayValue = array_map(function ($x) use ($values) {
            return isset($values[$x]) ? $values[$x] : '';
        }, $keys);

        return count($arrayValue) == 1 ? $arrayValue[0] : implode(', ', $arrayValue);
    }

    /**
     * User store work history
     *
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function store($data)
    {
        $dataWorkHistory = $this->makeSaveData($data);
        $dataWorkHistories = UserWorkHistory::query()->where('user_id', $dataWorkHistory['user_id'])->count();

        if ($dataWorkHistories >= UserWorkHistory::MAX_USER_WORK_HISTORY) {
            throw new InputException(trans('response.user_work_history.count_error'));
        }

        try {
            DB::beginTransaction();

            $jobTypeId = $this->checkNameObject($data['job_type_name'], WorkHistoryService::JOB_TYPES);
            $workTypeId = $this->checkNameObject($data['work_type_name']);
            $positionOfficesIds = $this->createObject($data['position_offices']);

            $dataWorkHistory = array_merge(
                ['job_type_id' => $jobTypeId],
                ['work_type_id' => $workTypeId],
                ['position_office_ids' => $positionOfficesIds],
                $this->makeSaveData($data)
            );
            UserWorkHistory::query()->create($dataWorkHistory);

            DB::commit();
            return $data;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception->getMessage());
        }//end try
    }

    /**
     * Detail user work history
     *
     * @param $userWorkHistoryId
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($userWorkHistoryId)
    {
        $user = $this->user;
        $userWorkHistory = UserWorkHistory::query()
            ->with(['jobType', 'workType'])
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $userWorkHistoryId)
            ->first();
        if (!$userWorkHistory) {
            throw new InputException(trans('response.not_found'));
        }

        $positionOffices = MPositionOffice::query()->whereIn('id', $userWorkHistory->position_office_ids)->get()->pluck('name')->toArray();
        $userWorkHistory['position_offices'] = $positionOffices;

        return $userWorkHistory;
    }

    /**
     * Update user work history
     *
     * @param $userWorkHistoryId
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function update($userWorkHistoryId, $data)
    {
        $user = $this->user;
        $userWorkHistory = UserWorkHistory::query()
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $userWorkHistoryId)
            ->first();
        if (!$userWorkHistory) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            $jobTypeId = $this->checkNameObject($data['job_type_name'], WorkHistoryService::JOB_TYPES);
            $workTypeId = $this->checkNameObject($data['work_type_name']);
            $positionOfficesIds = $this->createObject($data['position_offices']);

            $dataWorkHistory = array_merge(
                ['job_type_id' => $jobTypeId],
                ['work_type_id' => $workTypeId],
                ['position_office_ids' => $positionOfficesIds],
                $this->makeSaveData($data)
            );
            $userWorkHistory->update($dataWorkHistory);

            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception->getMessage());
        }//end try
    }

    /**
     * @param $name
     * @param string $object
     * @return HigherOrderBuilderProxy|mixed
     */
    public function checkNameObject($name, $object = WorkHistoryService::WORK_TYPES)
    {
        switch ($object) {
            case 'm_work_type':
                $object = MWorkType::query();
                break;
            default:
                $object = MJobType::query();
        }

        $dataObject = $object->where('name', '=', $name)->first();

        if ($dataObject) {
            return $dataObject->id;
        }

        return $object->create(['name' => $name])->id;
    }

    /**
     * @param $dataNames
     * @return array
     */
    public function createObject($dataNames)
    {
        $dataIds = [];
        $dataNameDiffs = [];
        foreach ($dataNames as $dataName) {
            if (isset($dataName['id'])) {
                $dataIds[] = $dataName['id'];
            } else {
                $dataNameDiffs[] = $dataName['name'];
            }
        }

        if (count($dataNameDiffs) > 0) {
            $inputsNameDiffs = [];
            $dataObjects = MPositionOffice::query()->get()->pluck('name')->toArray();
            $dataNameDuplicate = [];

            foreach ($dataNameDiffs as $dataNameDiff) {
                if (in_array($dataNameDiff, $dataObjects)) {
                    $dataNameDuplicate[] = $dataNameDiff;
                } else {
                    $inputsNameDiffs[] = [
                        'name' => $dataNameDiff
                    ];
                }
            }


            MPositionOffice::query()->insert($inputsNameDiffs);
            $inputsNameDiffs = array_merge($inputsNameDiffs, $dataNameDuplicate);
            $dataNameIds = MPositionOffice::query()
                ->whereIn('name', $inputsNameDiffs)
                ->get()
                ->pluck('id')
                ->toArray();

            $dataIds = array_merge($dataIds, $dataNameIds);
        }//end if

        return $dataIds;
    }

    /**
     * Delete user work history
     *
     * @param $userWorkHistoryId
     * @return bool|mixed|null
     * @throws InputException
     */
    public function delete($userWorkHistoryId)
    {
        $user = $this->user;
        $userWorkHistory = UserWorkHistory::query()
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $userWorkHistoryId)
            ->first();
        if (!$userWorkHistory) {
            throw new InputException(trans('response.not_found'));
        }

        return $userWorkHistory->delete();
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
            'store_name' => $data['store_name'],
            'company_name' => $data['company_name'],
            'period_start' => str_replace('/', '', $data['period_start']),
            'period_end' => str_replace('/', '', $data['period_end']),
            'business_content' => $data['business_content'],
            'experience_accumulation' => $data['experience_accumulation'],
        ];
    }
}
