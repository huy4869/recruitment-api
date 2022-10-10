<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\MJobType;
use App\Models\MPositionOffice;
use App\Models\MWorkType;
use App\Models\UserWorkHistory;
use App\Services\Service;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        return UserWorkHistory::query()
            ->where('user_id', $user->id)
            ->with(['workType', 'jobType'])
            ->orderBy('period_end', 'DESC')
            ->get();
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
        $dataWorkHistory =  $this->makeSaveData($data);
        $dataWorkHistories = UserWorkHistory::query()->where('user_id', $dataWorkHistory['user_id'])->count();

        if ($dataWorkHistories > 10) {
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
        }
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

        $dataObjectName = $object->whereIn('name', $dataNames)->get();
        $dataNameDiffs = array_diff(collect($dataNames)->pluck('name')->toArray(), $dataObjectName->pluck('name')->toArray());
        $dataObjectIds = $dataObjectName->pluck('id')->toArray();

        if (count($dataNameDiffs) > 0) {
            $inputsNameDiffs = [];
            foreach ($dataNameDiffs as $dataNameDiff) {
                $inputsNameDiffs[] = [
                    'name' => $dataNameDiff
                ];
            }
            $object->insert($inputsNameDiffs);
            $dataTagsNameIds = $object
                ->whereIn('name', $inputsNameDiffs)
                ->get()
                ->pluck('id')
                ->toArray();

            $dataObjectIds = array_merge($dataObjectIds, $dataTagsNameIds);
        }

        return json_encode($dataObjectIds);
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
            'period_start' => DateTimeHelper::formatDateWorkHistoryBe($data['period_start']),
            'period_end' => DateTimeHelper::formatDateWorkHistoryBe($data['period_end']),
            'business_content' => $data['business_content'],
            'experience_accumulation' => $data['experience_accumulation'],
        ];
    }
}
