<?php

namespace App\Services\Admin;

use App\Exceptions\InputException;
use App\Models\User;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthService extends Service
{
    /**
     * Login
     *
     * @param array $data
     * @return array|null
     * @throws InputException
     */
    public function login(array $data)
    {
        $admin = User::query()->where('email', $data['email'])->roleAdmin()->first();

        if (!$admin) {
            throw new InputException(trans('validation.exists', [
                'attribute' => trans('validation.attributes.email')
            ]));
        }

        if (!Hash::check($data['password'], $admin->password)) {
            throw new InputException(trans('validation.custom.wrong_password'));
        }

        $token = $admin->createToken('authAdminToken', ['*'], Carbon::now()
            ->addDays(config('validate.token_expire')))->plainTextToken;

        return [
            'access_token' => $token,
            'type_token' => 'Bearer',
        ];
    }

    /**
     * Update profile
     *
     * @param $data
     * @return int
     * @throws InputException
     */
    public function update($data)
    {
        $admin = $this->user;
        if (!$admin) {
            throw new InputException(trans('response.not_found'));
        }

        if ($admin->status == Admin::STATUS_INACTIVE) {
            throw new InputException(trans('response.invalid'));
        }

        return Admin::query()
            ->where('id', '=', $admin->id)
            ->update($data);
    }

    /**
     * Change Password
     *
     * @param array $data
     * @return bool
     * @throws InputException
     */
    public function changePassword(array $data)
    {
        $admin = $this->user;

        if (!Hash::check($data['current_password'], $admin->password)) {
            throw new InputException(trans('auth.password'));
        }

        $admin->update([
            'password' => Hash::make($data['password'])
        ]);

        return true;
    }
}
