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
     * @param JobPosting $jobPosting
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function store(JobPosting $jobPosting, $data)
    {
        if (!in_array(FeedbackJob::FEEDBACK_TYPE_DESIRE_SALARY, $data['feedback_type_ids'])
            && isset($data['desired_salary'])
        ) {
            throw new InputException(trans('validation.ERR.012'));
        }

        $data = array_merge($data, [
            'job_posting_id' => $jobPosting->id,
            'user_id' => $this->user->id,
            'type' => $this->user->role_id,
        ]);

        return FeedbackJob::create($data);
    }
}
