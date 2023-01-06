<?php

namespace App\Services\Admin\Application;

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
        'owner' => 'filterOwner',
        'user_furi_name' => 'filterName',
        'created_at_from' => 'filterByCreatedAt',
        'created_at_to' => 'filterByCreatedAt',
        'interview_status_id' => 'applications.interview_status_id',
        'stores.province_id' => 'stores.province_id',
        'stores.id' => 'stores.id'
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
     * @param $query
     * @param $filter
     * @return mixed
     */
    protected function filterOwner($query, $filter)
    {
        if (!count($filter) || !is_string($filter['data'])) {
            return $query;
        }

        $queryKeys = [
            'users.first_name',
            'users.last_name',
            'CONCAT(users.first_name, " ",users.last_name)',
        ];

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
        return Application::query()
            ->selectRaw($this->getSelectRaw())
            ->join('application_users', 'applications.id', '=', 'application_users.application_id')
            ->join('job_postings', 'job_posting_id', '=', 'job_postings.id')
            ->join('stores', 'applications.store_id', '=', 'stores.id')
            ->join('users', 'stores.user_id', '=', 'users.id')
            ->with([
                'interviews',
                'applicationUserTrash.avatarBanner'
            ])
            ->withTrashed()
            ->orderBy('created_at', 'DESC');
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
            applications.deleted_at,
            applications.user_id,
            applications.updated_at,
            application_users.first_name,
            application_users.last_name,
            application_users.furi_first_name,
            application_users.furi_last_name,
            application_users.birthday,
            application_users.is_public_avatar,
            application_users.age,
            users.first_name as owner_first_name,
            users.last_name as owner_last_name,
            stores.id as store_id,
            stores.name as store_name,
            stores.province_id as store_province_id';
    }
}
