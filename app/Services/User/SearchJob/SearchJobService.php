<?php

namespace App\Services\User\SearchJob;

use App\Exceptions\InputException;
use App\Models\SearchJob;
use App\Services\Service;

class SearchJobService extends Service
{
    /**
     * @param $data
     * @return mixed
     */
    public function store($data)
    {
        $storeData = [
            'user_id' => $this->user->id,
            'content' => $data,
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
