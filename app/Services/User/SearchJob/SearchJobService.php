<?php

namespace App\Services\User\SearchJob;

use App\Exceptions\InputException;
use App\Models\SearchJob;
use App\Services\Service;

class SearchJobService extends Service
{
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
