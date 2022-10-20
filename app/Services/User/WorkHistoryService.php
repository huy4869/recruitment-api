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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkHistoryService extends Service
{
    const JOB_TYPES = 'job_types';
    const WORK_TYPES = 'work_types';
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
            ->where('user_id', $user->id)
            ->orderBy('period_end', 'DESC')
            ->get()
            ->toArray();

        $jobTypes = MJobType::all()->pluck('name', 'id')->toArray();
        $workTypes = MWorkType::all()->pluck('name', 'id')->toArray();
        $positionOffices = MPositionOffice::all()->pluck('name', 'id')->toArray();

        foreach ($userWorkHistories as $key => $userWorkHistory) {
            $jobTypeIds = json_decode($userWorkHistory['job_type_ids']);
            $workTypeIds = json_decode($userWorkHistory['work_type_ids']);
            $positionOfficeIds = json_decode($userWorkHistory['position_office_ids']);
            $userWorkHistories[$key]['job_types'] = $this->getArrayValueByKeys($jobTypes, $jobTypeIds);
            $userWorkHistories[$key]['work_types'] = $this->getArrayValueByKeys($workTypes, $workTypeIds);
            $userWorkHistories[$key]['position_offices'] = $this->getArrayValueByKeys($positionOffices, $positionOfficeIds);
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
        $keys = array_map('strval', $keys);
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

            $jobTypeIds = $this->createObject($data['job_types'], WorkHistoryService::JOB_TYPES);
            $workTypeIds = $this->createObject($data['work_types'], WorkHistoryService::WORK_TYPES);
            $positionOfficesIds = $this->createObject($data['position_offices']);

            $dataWorkHistory = array_merge(
                ['job_type_ids' => $jobTypeIds],
                ['work_type_ids' => $workTypeIds],
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
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $userWorkHistoryId)
            ->first();
        if (!$userWorkHistory) {
            throw new InputException(trans('response.not_found'));
        }

        $jobTypeIds = json_decode($userWorkHistory->job_type_ids);
        $workTypeIds = json_decode($userWorkHistory->work_type_ids);
        $positionOfficeIds = json_decode($userWorkHistory->position_office_ids);
        $jobTypes = MJobType::query()->whereIn('id', $jobTypeIds)->get()->pluck('name')->toArray();
        $workTypes = MWorkType::query()->whereIn('id', $workTypeIds)->get()->pluck('name')->toArray();
        $positionOffices = MPositionOffice::query()->whereIn('id', $positionOfficeIds)->get()->pluck('name')->toArray();
        $userWorkHistory['job_types'] = $jobTypes;
        $userWorkHistory['work_types'] = $workTypes;
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

            $jobTypeIds = $this->createObject($data['job_types'], WorkHistoryService::JOB_TYPES);
            $workTypeIds = $this->createObject($data['work_types'], WorkHistoryService::WORK_TYPES);
            $positionOfficesIds = $this->createObject($data['position_offices']);

            $dataWorkHistory = array_merge(
                ['job_type_ids' => $jobTypeIds],
                ['work_type_ids' => $workTypeIds],
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
     *
     * @param $dataNames
     * @param string $object
     * @return false|string
     */
    public function createObject($dataNames, $object = WorkHistoryService::POSITION_OFFICES)
    {
        switch ($object) {
            case 'job_types':
                $object = MJobType::query();
                break;
            case 'work_types':
                $object = MWorkType::query();
                break;
            default:
                $object = MPositionOffice::query();
        }

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
            $dataObjects = $object->get()->pluck('name')->toArray();
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


            $object->insert($inputsNameDiffs);
            $inputsNameDiffs = array_merge($inputsNameDiffs, $dataNameDuplicate);
            $dataNameIds = $object
                ->whereIn('name', $inputsNameDiffs)
                ->get()
                ->pluck('id')
                ->toArray();

            $dataIds = array_merge($dataIds, $dataNameIds);
        }//end if

        return json_encode($dataIds);
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
