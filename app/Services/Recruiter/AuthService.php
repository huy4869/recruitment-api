<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
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
     * @return array
     * @throws InputException
     */
    public function login(array $data)
    {
        $recruiter = User::query()->where('email', '=', $data['email'])->roleRecruiter()->first();

        if (!$recruiter) {
            throw new InputException(trans('validation.exists', [
                'attribute' => trans('validation.attributes.email')
            ]));
        }

        if (!Hash::check($data['password'], $recruiter->password)) {
            throw new InputException(trans('validation.custom.wrong_password'));
        }

        $token = $recruiter->createToken('authRecruiterToken', [], Carbon::now()
            ->addDays(config('validate.token_expire')))->plainTextToken;

        $recruiter->update([
            'last_login_at' => now(),
        ]);

        return [
            'access_token' => $token,
            'type_token' => 'Bearer',
        ];
    }

    /**
     * register recruiter
     *
     * @param array $data
     * @return mixed
     * @throws InputException
     */
    public function register(array $data)
    {
        $newUser = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'furi_first_name' => $data['furi_first_name'],
            'furi_last_name' => $data['furi_last_name'],
            'role_id' => User::ROLE_RECRUITER,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!$newUser) {
            throw new InputException(trans('auth.register_fail'));
        }

        return $newUser;
    }

    /**
     * @param $password
     * @return bool
     */
    public function changePassword($data)
    {
        $user = $this->user;

        return $user->update([
            'password' => Hash::make($data['password'])
        ]);
    }
}
