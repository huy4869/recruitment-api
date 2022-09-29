<?php

namespace App\Services\Recruiter;

use App\Exceptions\InputException;
use App\Models\User;
use App\Services\Service;
use Illuminate\Support\Facades\Hash;

class AuthService extends Service
{
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
            'role_id' => User::ROLE_RECRUITER,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        
        if (!$newUser) {
            throw new InputException(trans('auth.register_fail'));
        }

        return $newUser;
    }
}
