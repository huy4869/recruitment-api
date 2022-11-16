<?php

namespace App\Services\User\SearchJob;

use App\Exceptions\InputException;
use App\Models\SearchJob;
use App\Services\Service;

class SearchJobService extends Service
{
    const ORDER_BY_CREATED_AT = 1;
    const ORDER_BY_UPDATED_AT = 2;

    /**
     * @param $search
     * @param $filters
     * @return mixed
     */
    public function store($search, $filters)
    {
        $searchData = [];

        if ($search) {
            $searchData = array_merge($searchData, ['search' => $search]);
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $searchData = array_merge($searchData, [
                    $filter['key'] => json_decode($filter['data'])
                ]);
            }
        }

        $storeData = [
            'user_id' => $this->user->id,
            'content' => $searchData,
        ];

        return SearchJob::create($storeData);
    }

    /**
     * @param $id
     * @return bool
     * @throws InputException
     */
    public function destroy($id)
    {
        $userSearch = SearchJob::query()->where('user_id', $this->user->id)
            ->where('id', $id)->first();

        if (!$userSearch) {
            throw new InputException(trans('response.not_found'));
        }

        return $userSearch->delete();
    }
}
