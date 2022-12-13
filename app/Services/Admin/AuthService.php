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
            throw new InputException(trans('validation.COM.006', [
                'attribute' => trans('validation.attributes.email')
            ]));
        }

        if (!Hash::check($data['password'], $admin->password)) {
            throw new InputException(trans('validation.ERR.001'));
        }

        $token = $admin->createToken('authAdminToken', ['*'], Carbon::now()
            ->addDays(config('validate.token_expire')))->plainTextToken;

        $admin->update([
            'last_login_at' => now(),
        ]);

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

        return $admin->update([
            'password' => Hash::make($data['password'])
        ]);
    }

    /**
     * create User|Admin|Rec
     *
     * @param array $data
     * @return mixed
     * @throws InputException
     */
    public function register(array $data)
    {
        if ($data['role_id'] == User::ROLE_ADMIN) {
            throw new InputException(trans('validation.has_not_permission'));
        }

        if ($this->user->role_id === User::ROLE_SUB_ADMIN && $data['role_id'] == User::ROLE_SUB_ADMIN) {
            throw new InputException(trans('validation.has_not_permission'));
        }

        return User::create([
            'alias_name' => $data['alias_name'],
            'role_id' => $data['role_id'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
