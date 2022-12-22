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
    const JOB_TYPES = 'm_job_types';
    const WORK_TYPES = 'm_work_types';
    const POSITION_OFFICES = 'position_offices';
    const START_ID_FOR_NOT_DEFAULT_RECORD = 1000;

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
            ->orderByRaw('period_end is not null, period_end DESC, period_start DESC')
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

        return count($arrayValue) == 1 ? $arrayValue[0] : implode('、', $arrayValue);
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
        try {
            DB::beginTransaction();

            $jobTypeId = $this->buildIndexFromObject($data['job_types'], WorkHistoryService::JOB_TYPES);
            $workTypeId = $this->buildIndexFromObject($data['work_types']);
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

        $positionOffices = MPositionOffice::query()->whereIn('id', $userWorkHistory->position_office_ids)->get();
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

            $jobTypeId = $this->buildIndexFromObject($data['job_types'], WorkHistoryService::JOB_TYPES);
            $workTypeId = $this->buildIndexFromObject($data['work_types']);
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
     * @param $types
     * @param string $object
     * @return HigherOrderBuilderProxy|mixed
     */
    public function buildIndexFromObject($types, $object = WorkHistoryService::WORK_TYPES)
    {
        $dataObject = DB::table($object)->where('name', '=', $types['name'])->first();

        if ($dataObject) {
            return $dataObject->id;
        }

        $lastCurrentRecord = DB::table($object)->where('is_default', MWorkType::NO_DEFAULT)
            ->latest('id')
            ->first();

        if ($lastCurrentRecord) {
            $lastId = $lastCurrentRecord->id + 1;
        } else {
            $lastId = self::START_ID_FOR_NOT_DEFAULT_RECORD;
        }

        DB::table($object)->insert([
            'id' => $lastId,
            'name' => $types['name'],
            'is_default' => MWorkType::NO_DEFAULT,
            'created_at' => now(),
        ]);

        return $lastId;
    }

    /**
     * Get type ids
     *
     * @param $object
     * @return mixed
     */
    public function getTypeIds($object)
    {
        return $object->where('is_default', '=', MJobType::IS_DEFAULT)->get()->pluck('id')->toArray();
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
            $dataObjects = MPositionOffice::query()
                ->where('is_default', '=', MPositionOffice::IS_DEFAULT)
                ->orwhere('created_by', '=', $this->user->id)
                ->get()->pluck('name')->toArray();
            $dataNameDuplicate = [];
            $dataInputName = [];
            $key = 1;

            foreach ($dataNameDiffs as $dataNameDiff) {
                if (in_array($dataNameDiff, $dataObjects)) {
                    $dataNameDuplicate[] = $dataNameDiff;
                } else {
                    $lastCurrentRecord = MPositionOffice::query()
                        ->where('is_default', MWorkType::NO_DEFAULT)
                        ->latest('id')
                        ->first();

                    if ($lastCurrentRecord) {
                        $lastId = $lastCurrentRecord->id + $key;
                    } else {
                        $lastId = self::START_ID_FOR_NOT_DEFAULT_RECORD;
                    }

                    $inputsNameDiffs[] = [
                        'id' => $lastId,
                        'name' => $dataNameDiff,
                        'is_default' => MPositionOffice::NO_DEFAULT,
                        'created_by' => $this->user->id,
                    ];
                    $dataInputName[] = $dataNameDiff;
                    $key++;
                }
            }

            count($inputsNameDiffs) && MPositionOffice::query()->insert($inputsNameDiffs);
            $inputsNameDiffs = array_merge($dataInputName, $dataNameDuplicate);
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
            'period_end' => $data['period_end'] ? str_replace('/', '', $data['period_end']) : NULL,
            'business_content' => $data['business_content'],
            'experience_accumulation' => $data['experience_accumulation'],
        ];
    }
}
