<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Models\User;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService extends Service
{
    /**
     * Login
     *
     * @param array $data
     * @return array
     * @throws InputException
     */
    public function login(array $data)
    {
        $user = User::query()->where('email', '=', $data['email'])->roleUser()->first();

        if (!$user) {
            throw new InputException(trans('validation.exists', [
                'attribute' => trans('validation.attributes.email')
            ]));
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw new InputException(trans('validation.custom.wrong_password'));
        }

        $token = $user->createToken('authUserToken', [], Carbon::now()
            ->addDays(config('validate.token_expire')))->plainTextToken;

        $user->update([
            'last_login_at' => now(),
        ]);

        return [
            'access_token' => $token,
            'type_token' => 'Bearer',
        ];
    }

    /**
     * Register
     *
     * @param array $data
     * @return mixed
     * @throws InputException
     */
    public function register(array $data)
    {
        $newUser = User::query()->create([
            'email' => Str::lower($data['email']),
            'password' => Hash::make($data['password']),
            'role_id' => User::ROLE_USER,
        ]);
        if (!$newUser) {
            throw new InputException(trans('auth.register_fail'));
        }

        return $newUser;
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
        $user = $this->user;
        if (!$user) {
            throw new InputException(trans('response.not_found'));
        }

        if ($user->status == User::STATUS_INACTIVE) {
            throw new InputException(trans('response.invalid'));
        }

        return User::query()
            ->where('id', '=', $user->id)
            ->update($data);
    }

    /**
     * Change Password
     *
     * @param array $data
     * @return bool
     */
    public function changePassword(array $data)
    {
        $user = $this->user;

        return $user->update([
            'password' => Hash::make($data['password'])
        ]);
    }
}
