<?php

namespace App\Services\Admin\User;

use App\Exceptions\InputException;
use App\Models\User;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserService extends Service
{
    /**
     * Detail user
     *
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($id)
    {
        $user = User::query()->where('id', $id)->first();
        if (!$user) {
            throw new InputException(trans('response.not_found'));
        }

        return $user;
    }

    /**
     * Update user
     *
     * @param $id
     * @param array $data
     * @return mixed
     * @throws InputException
     */
    public function update($id, array $data)
    {
        $user = User::query()->where('id', $id)->first();
        if (!$user) {
            throw new InputException(trans('response.not_found'));
        }

        return $user->update($data);
    }
}
