<?php

namespace App\Services\Admin\User;

use App\Helpers\StringHelper;
use App\Models\User;
use App\Services\Common\SearchService;
use App\Services\TableService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserTableService extends TableService
{
    /**
     * @var string[]
     */
    protected $searchables = [];

    /**
     * @var string[]
     */
    protected $filterables = [
        'role_id' => 'filterRole',
        'user_name' => 'searchUserName',
        'email' => 'search',
    ];

    /**
     * @var string[]
     */
    protected $orderables = [];

    /**
     * @param $query
     * @param $filter
     * @return mixed
     */
    protected function filterRole($query, $filter)
    {
        $types = SearchService::encodeStringToArray($filter['data']);

        return $query->whereIn($filter['key'], $types);
    }

    /**
     * @param $query
     * @param $search
     * @return Builder
     */
    protected function search($query, $search)
    {
        return $query->where($search['key'], 'like', '%' . $search['data'] . '%');
    }

    /**
     * @param $query
     * @param $filter
     * @return Builder
     */
    protected function searchUserName($query, $filter)
    {
        if (!count($filter) || !is_string($filter['data'])) {
            return $query;
        }

        switch ($filter['key']) {
            case 'user_name':
                $queryKeys = [
                    'users.first_name',
                    'users.last_name',
                    'CONCAT(users.first_name, " ",users.last_name)',
                ];
                break;
            case 'user_furi_name':
                $queryKeys = [
                    'users.furi_first_name',
                    'users.furi_last_name',
                    'CONCAT(users.furi_first_name, " ",users.furi_last_name)',
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
        return User::query()
            ->whereNot('role_id', User::ROLE_ADMIN)
            ->whereNot('id', $this->user->id)
            ->with([
                'role',
                'stores'
            ])
            ->orderBy('last_login_at', 'desc')
            ->selectRaw($this->getSelectRaw());
    }

    /**
     * Get Select Raw
     *
     * @return string
     */
    protected function getSelectRaw()
    {
        return 'users.id,
            users.role_id,
            users.first_name,
            users.last_name,
            users.email,
            users.last_login_at,
            users.created_at';
    }
}
