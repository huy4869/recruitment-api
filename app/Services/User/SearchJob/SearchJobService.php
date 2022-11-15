<?php

namespace App\Services\User\SearchJob;

use App\Exceptions\InputException;
use App\Models\SearchJob;
use App\Services\Service;

class SearchJobService extends Service
{
    /**
     * @param $search
     * @param $orders
     * @param $filters
     * @return mixed
     */
    public function store($search, $orders, $filters)
    {
        $searchData = [];

        if ($search) {
            $searchData = array_merge($searchData, ['text' => $search]);
        }

        if ($filters) {
            foreach ($filters as $filter) {
                $searchData = array_merge($searchData, [
                    $filter['key'] => json_decode($filter['data'])
                ]);
            }
        }

        if ($orders) {
            foreach ($orders as $order) {
                $searchData = array_merge($searchData, [
                    $order['key'] => json_decode($order['data'])
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
