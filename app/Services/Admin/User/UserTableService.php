<?php

namespace App\Services\Admin\User;

use App\Models\User;
use App\Services\TableService;

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
        'status' => 'users.status',
    ];

    /**
     * @var string[]
     */
    protected $orderables = [
        'status' => 'users.status',
        'created_at' => 'users.created_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function makeNewQuery()
    {
        return User::query()->selectRaw($this->getSelectRaw());
    }

    /**
     * Get Select Raw
     *
     * @return string
     */
    protected function getSelectRaw()
    {
        $fields = [
            'users.id',
            'users.name',
            'users.email',
            'users.status',
            'users.created_at',
        ];
        return implode(', ', $fields);
    }
}
