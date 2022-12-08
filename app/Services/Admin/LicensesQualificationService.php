<?php

namespace App\Services\Admin;

use App\Exceptions\InputException;
use App\Models\User;
use App\Models\UserLicensesQualification;
use App\Services\Service;

class LicensesQualificationService extends Service
{
    /**
     * create licenses qualification
     *
     * @param $data
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function store($data, $userId)
    {
        $user = User::query()->roleUser()->where('id', $userId)->first();

        if (!$user) {
            throw new InputException(trans('validation.ERR.exist.user_not_exist'));
        }

        $dataCreate = [
            'user_id' => $userId,
            'name' => $data['name'],
            'new_issuance_date' => str_replace('/', '', $data['new_issuance_date']),
        ];

        return UserLicensesQualification::query()->create($dataCreate);
    }

    /**
     * update licenses qualification
     *
     * @param $data
     * @param $id
     * @param $userId
     * @return bool|int
     * @throws InputException
     */
    public function update($data, $id, $userId)
    {
        $licensesQualification = UserLicensesQualification::query()
            ->where([
                ['id', $id],
                ['user_id', $userId],
            ])
            ->first();

        if ($licensesQualification) {
            $dataUpdate = [
                'name' => $data['name'],
                'new_issuance_date' => str_replace('/', '', $data['new_issuance_date']),
            ];

            return $licensesQualification->update($dataUpdate);
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * @param $id
     * @param $user_id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     * @throws InputException
     */
    public function detail($id, $user_id)
    {
        $licensesQualification = UserLicensesQualification::query()
            ->where('user_id', '=', $user_id)
            ->where('id', '=', $id)
            ->first();

        if ($licensesQualification) {
            return $licensesQualification;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * @param $id
     * @param $user_id
     * @return bool|mixed|null
     * @throws InputException
     */
    public function delete($id, $user_id)
    {
        $licensesQualification = UserLicensesQualification::query()
            ->where('user_id', '=', $user_id)
            ->where('id', '=', $id)
            ->first();

        if ($licensesQualification) {
            return $licensesQualification->delete();
        }

        throw new InputException(trans('response.not_found'));
    }
}
