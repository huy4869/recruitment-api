<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\FavoriteJob;
use App\Services\Service;

class JobService extends Service
{
    /**
     * delete favorite job
     *
     * @return bool|null
     */
    public function deleteFavorite($id)
    {
        $data =  FavoriteJob::where('user_id', $this->user->id)->find($id);

        if ($data) {
            return $data->delete();
        }

        throw new InputException(trans('validation.ERR.exist.favorite_job'));
    }
}
