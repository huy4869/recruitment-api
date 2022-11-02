<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\FileHelper;
use App\Services\Common\FileService;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Image;
use App\Models\User;

class UserService extends Service
{
    /**
     * Update profile user
     *
     * @param $data
     * @return bool
     * @throws InputException
     */
    public function update($data)
    {
        $user = $this->user;
        try {
            DB::beginTransaction();

            $user->update($this->makeSaveData($data));
            FileService::getInstance()->updateImageable($user, $this->makeSaveDataImage($data));

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception->getMessage());
        }
    }

    /**
     * Make Save data images
     *
     * @param $data
     * @return array
     */
    private function makeSaveDataImage($data)
    {
        $dataUrl = [];
        foreach ($data['images'] as $image) {
            $dataUrl[] = FileHelper::fullPathNotDomain($image['url']);
        }

        return array_merge([FileHelper::fullPathNotDomain($data['avatar'])], $dataUrl);
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
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'alias_name' => $data['alias_name'],
            'furi_first_name' => $data['furi_first_name'],
            'furi_last_name' => $data['furi_last_name'],
            'birthday' => $data['birthday'],
            'age' => $data['age'],
            'gender_id' => $data['gender_id'],
            'tel' => $data['tel'],
            'line' => $data['line'],
            'facebook' => $data['facebook'],
            'instagram' => $data['instagram'],
            'twitter' => $data['twitter'],
            'postal_code' => $data['postal_code'],
            'province_id' => $data['province_id'],
            'province_city_id' => $data['province_city_id'],
            'city' => $data['city'],
            'address' => $data['address'],
        ];
    }

    /**
     * @return Builder
     */
    public function getBasicInfo()
    {
        return User::query()->with(['avatarDetails', 'avatarBanner', 'provinceCity', 'provinceCity.province'])->where('id', '=', $this->user->id)->first();
    }

    /**
     * @return User|null
     */
    public function getPrInformation()
    {
        return $this->user;
    }

    /**
     * Update Data
     *
     * @param $data
     * @return bool
     */
    public function updateInformationPr($data)
    {
        $user = $this->user;

        return $user->update($data);
    }

    /**
     * Update motivation
     *
     * @param $data
     * @return bool
     */
    public function updateMotivation($data)
    {
        $user = $this->user;

        return $user->update($data);
    }
}
