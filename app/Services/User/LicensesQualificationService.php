<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\DateTimeHelper;
use App\Models\UserLicensesQualification;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LicensesQualificationService extends Service
{
    /**
     * List user licenses qualification
     *
     * @return Builder[]|Collection
     */
    public function list()
    {
        return UserLicensesQualification::query()
            ->where('user_id', $this->user->id)
            ->orderBy('new_issuance_date', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    /**
     * User store licenses qualification
     *
     * @param $data
     * @return Builder|Model
     */
    public function store($data)
    {
        return UserLicensesQualification::query()->create($this->makeSaveData($data));
    }

    /**
     * Detail user licenses qualification
     *
     * @param $idLicensesQualification
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($idLicensesQualification)
    {
        return $this->checkDataObject($idLicensesQualification);
    }

    /**
     *  User update licenses qualification
     *
     * @param $idLicensesQualification
     * @param $data
     * @return bool|int
     * @throws InputException
     */
    public function update($idLicensesQualification, $data)
    {
        return $this->checkDataObject($idLicensesQualification)->update($this->makeSaveData($data));
    }

    /**
     * User delete licenses qualification
     *
     * @param $idLicensesQualification
     * @return bool|mixed|null
     * @throws InputException
     */
    public function delete($idLicensesQualification)
    {
        return $this->checkDataObject($idLicensesQualification)->delete();
    }

    /**
     * Check object user licenses qualification
     *
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    private function checkDataObject($id)
    {
        $licensesQualification = UserLicensesQualification::query()
            ->where('user_id', '=', $this->user->id)
            ->where('id', '=', $id)
            ->first();

        if ($licensesQualification) {
            return $licensesQualification;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * Make Save data
     *
     * @param $data
     * @return array
     */
    private function makeSaveData($data)
    {
        return [
            'user_id' => $this->user->id,
            'name' => $data['name'],
            'new_issuance_date' => str_replace('/', '', $data['new_issuance_date']),
        ];
    }
}
