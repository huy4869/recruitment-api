<?php

namespace App\Services\Admin\FeedbackJobs;

use App\Exceptions\InputException;
use App\Models\FeedbackJob;
use App\Services\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FeedbackJobsService extends Service
{
    public const PER_PAGE = 10;

    /**
     * List feedback job
     *
     * @param $perPage
     * @return array|LengthAwarePaginator
     */
    public function list($perPage)
    {
        $perPage = $perPage ?? self::PER_PAGE;

        return FeedbackJob::query()->withTrashed()->with('userTrashed')->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get data object
     *
     * @param $data
     * @param $object
     * @return array
     */
    public static function getDataObject($data, $object)
    {
        $dataObject = $object->get()->pluck('name', 'id')->toArray();
        $results = [];

        foreach ($data as $item) {
            $results[] = [
                'id' => $item,
                'name' => $dataObject[$item]
            ];
        }

        return $results;
    }

    /**
     * Detail feedback job
     *
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($id)
    {
        $feedbackJob = FeedbackJob::query()->withTrashed()->with('userTrashed')->where('id', '=', $id)->first();

        if ($feedbackJob) {
            if ($feedbackJob->be_read == FeedbackJob::NOT_READ) {
                $feedbackJob->update(['be_read' => FeedbackJob::BE_READ]);
            }

            return $feedbackJob;
        }

        throw new InputException(trans('response.not_found'));
    }
}
