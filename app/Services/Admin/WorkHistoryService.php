<?php

namespace App\Services\Admin;

use App\Exceptions\InputException;
use App\Models\MJobType;
use App\Models\MPositionOffice;
use App\Models\MWorkType;
use App\Models\User;
use App\Models\UserWorkHistory;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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
     * create work history
     *
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function store($data, $user_id)
    {
        $user = User::query()->where('id', $user_id)->roleUser()->first();

        if (!$user) {
            throw new InputException(trans('validation.ERR.exist.user_not_exist'));
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
                $data
            );

            $dataWorkHistory['period_start'] = str_replace('/', '', $data['period_start']);
            $dataWorkHistory['period_end'] = str_replace('/', '', $data['period_end']);
            $dataWorkHistory['user_id'] = $user_id;

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
            $dataObjects = MPositionOffice::query()->pluck('name')->toArray();
            $dataNameDuplicate = [];

            foreach ($dataNameDiffs as $dataNameDiff) {
                if (in_array($dataNameDiff, $dataObjects)) {
                    $dataNameDuplicate[] = $dataNameDiff;
                } else {
                    $lastCurrentRecord = MPositionOffice::query()
                        ->where('is_default', MWorkType::NO_DEFAULT)
                        ->latest()
                        ->first();

                    if ($lastCurrentRecord) {
                        $lastId = $lastCurrentRecord->id + 1;
                    } else {
                        $lastId = self::START_ID_FOR_NOT_DEFAULT_RECORD;
                    }

                    $inputsNameDiffs[] = [
                        'id' => $lastId,
                        'name' => $dataNameDiff,
                        'is_default' => MPositionOffice::NO_DEFAULT,
                        'created_by' => $this->user->id,
                    ];
                }
            }


            count($inputsNameDiffs) && MPositionOffice::query()->insert($inputsNameDiffs);
            $inputsNameDiffs = array_merge($inputsNameDiffs, $dataNameDuplicate);
            $dataNameIds = MPositionOffice::query()
                ->whereIn('name', $inputsNameDiffs)
                ->pluck('id')
                ->toArray();

            $dataIds = array_merge($dataIds, $dataNameIds);
        }//end if

        return $dataIds;
    }

    /**
     * Get type ids
     *
     * @param $object
     * @return mixed
     */
    public function getTypeIds($object)
    {
        return $object->where('is_default', '=', MJobType::IS_DEFAULT)->pluck('id')->toArray();
    }

    /**
     * Update user work history
     *
     * @param $userWorkHistoryId
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function update($data, $userWorkHistoryId, $user_id)
    {
        $userWorkHistory = UserWorkHistory::query()
            ->where('user_id', '=', $user_id)
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
                $data
            );

            $dataWorkHistory['period_start'] = str_replace('/', '', $data['period_start']);
            $dataWorkHistory['period_end'] = str_replace('/', '', $data['period_end']);

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
     * @param $id
     * @param $user_id
     * @return array|Builder|Model|object
     * @throws InputException
     */
    public function detail($id, $user_id)
    {
        $userWorkHistory = UserWorkHistory::query()
            ->with(['jobType', 'workType'])
            ->where('user_id', '=', $user_id)
            ->where('id', '=', $id)
            ->first();
        if (!$userWorkHistory) {
            throw new InputException(trans('response.not_found'));
        }

        $positionOffices = MPositionOffice::query()->whereIn('id', $userWorkHistory->position_office_ids)->get();
        $userWorkHistory['position_offices'] = $positionOffices;
        $userWorkHistory['position_office_options'] = MPositionOffice::where('is_default', MPositionOffice::IS_DEFAULT)->orWhere('created_by', $user_id)->get();;

        return $userWorkHistory;
    }

    /**
     * @param $id
     * @param $user_id
     * @return bool|mixed|null
     * @throws InputException
     */
    public function delete($id, $user_id)
    {
        $userWorkHistory = UserWorkHistory::query()
            ->where('user_id', '=', $user_id)
            ->where('id', '=', $id)
            ->first();
        if (!$userWorkHistory) {
            throw new InputException(trans('response.not_found'));
        }

        return $userWorkHistory->delete();
    }
}
