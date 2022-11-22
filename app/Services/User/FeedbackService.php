<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\FeedbackJob;
use App\Models\JobPosting;
use App\Services\Service;

class FeedbackService extends Service
{
    /**
     * Create feedback
     *
     * @param $jobId
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function store($jobId, $data)
    {
        $jobPosting = JobPosting::query()->where('id', $jobId)->released()->first();

        if ($jobPosting) {
            $data['feedback_type_ids'] = collect($data['feedback_type_ids'])->unique()->toArray();

            $data = array_merge($data, [
                'job_posting_id' => $jobPosting->id,
                'user_id' => $this->user->id,
                'type' => $this->user->role_id,
            ]);

            return FeedbackJob::create($data);
        }

        throw new InputException(trans('response.not_found'));
    }
}
