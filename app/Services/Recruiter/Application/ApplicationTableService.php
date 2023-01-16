<?php

namespace App\Services\Recruiter\Application;

use App\Helpers\StringHelper;
use App\Models\Application;
use App\Services\TableService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplicationTableService extends TableService
{
    /**
     * @var array
     */
    protected $searchables = [
        //
    ];

    /**
     * @var string[]
     */
    protected $filterables = [
        'job_name' => 'filterName',
        'user_name' => 'filterName',
        'user_furi_name' => 'filterName',
        'created_at_from' => 'filterByCreatedAt',
        'created_at_to' => 'filterByCreatedAt',
        'interview_status_id' => 'applications.interview_status_id',
    ];

    /**
     * @var string[]
     */
    protected $orderables = [
        //
    ];

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    protected function filterByCreatedAt($query, $filter)
    {
        if (!count($filter)) {
            return $query;
        }

        switch ($filter['key']) {
            case 'created_at_from':
                $comparisonOperator = '>=';
                break;
            case 'created_at_to':
                $comparisonOperator = '<=';
                break;
            default:
                $comparisonOperator = '=';
        }

        return $query->whereDate('applications.created_at', $comparisonOperator, $filter['data']);
    }

    /**
     * @param $query
     * @param $filter
     * @return Builder
     */
    protected function filterName($query, $filter)
    {
        if (!count($filter) || !is_string($filter['data'])) {
            return $query;
        }

        $queryKeys = [];

        switch ($filter['key']) {
            case 'user_name':
                $queryKeys = [
                    'application_users.first_name',
                    'application_users.last_name',
                    'CONCAT(application_users.first_name, " ",application_users.last_name)',
                ];
                break;
            case 'user_furi_name':
                $queryKeys = [
                    'application_users.furi_first_name',
                    'application_users.furi_last_name',
                    'CONCAT(application_users.furi_first_name, " ",application_users.furi_last_name)',
                ];
                break;
            case 'job_name':
                $queryKeys = [
                    'job_postings.name'
                ];
                break;
            default:
                return $query;
        }//end switch

        $filter['data'] = StringHelper::escapeLikeSearch($filter['data']);
        $content = '%' . trim($filter['data']) . '%';
        $query->where(function ($q) use ($content, $queryKeys) {
            foreach ($queryKeys as $key) {
                $q->orWhere(DB::raw($key), 'like', $content);
            }
        });

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function makeNewQuery()
    {
        $recruiter = $this->user;
        $recruiterStores = $recruiter->storeTrashed()->with(['jobs' => function ($q) {
            $q->withTrashed();
        }])->get();
        $jobIds = [];

        foreach ($recruiterStores as $store) {
            foreach ($store->jobs as $job) {
                $jobIds[] = $job->id;
            }
        }

        return Application::query()
            ->selectRaw($this->getSelectRaw())
            ->join('application_users', 'applications.id', '=', 'application_users.application_id')
            ->join('job_postings', 'job_posting_id', '=', 'job_postings.id')
            ->with([
                'interviews',
                'applicationUserTrash',
                'applicationUserTrash.avatarBanner'
            ])
            ->withTrashed()
            ->whereIn('job_posting_id', $jobIds)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get Select Raw
     *
     * @return string
     */
    protected function getSelectRaw()
    {
        return 'applications.id,
            applications.job_posting_id as job_id,
            job_postings.name as job_name,
            applications.interview_status_id,
            applications.created_at,
            applications.user_id,
            applications.updated_at,
            application_users.first_name,
            application_users.last_name,
            application_users.furi_first_name,
            application_users.furi_last_name,
            application_users.is_public_avatar,
            applications.deleted_at,
            application_users.age';
    }
}
