<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\UrlHelper;
use App\Jobs\User\JobVerifyRegister;
use App\Models\User;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            return [];
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw new InputException(trans('validation.custom.wrong_password'));
        }

        if (is_null($user->email_verified_at)) {
            throw new InputException(__('validation.ERR.051'));
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
        try {
            DB::beginTransaction();
            $newUser = User::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'furi_first_name' => $data['furi_first_name'],
                'furi_last_name' => $data['furi_last_name'],
                'email' => Str::lower($data['email']),
                'password' => Hash::make($data['password']),
                'role_id' => User::ROLE_USER,
                'verify_token' => Str::random(config('password_reset.token.length_verify')),
            ]);

            if (!$newUser) {
                throw new InputException(__('auth.register_fail'));
            }

            $this->sendMailVerifyRegister($newUser);

            DB::commit();
            return $newUser;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception->getMessage());
        }//end try
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

    /**
     * Send mail verify register
     *
     * @param $newUser
     */
    public function sendMailVerifyRegister($newUser)
    {
        $token = Crypt::encryptString($newUser->email . '&' . $newUser->verify_token);
        $url = UrlHelper::verifyRegisterLink($token, $newUser);

        $infoSendMail = [
            'email' => $newUser->email,
            'subject' => trans('mail.subject.verify_account'),
            'url' => $url,
        ];

        dispatch(new JobVerifyRegister($infoSendMail))->onQueue(config('queue.email_queue'));
    }

    /**
     * Check verify register
     *
     * @param $token
     * @return mixed
     * @throws InputException
     */
    public function verifyRegister($token)
    {
        $stringCrypt = Crypt::decryptString($token);
        $verifyRegister = explode('&', $stringCrypt);

        $user = User::query()
            ->roleUser()
            ->where('email', '=', $verifyRegister[0])
            ->where('verify_token', '=', $verifyRegister[1])
            ->first();

        if ($user) {
            $token = $user->createToken('authUserToken', [], Carbon::now()
                ->addDays(config('validate.token_expire')))->plainTextToken;

            $user->update([
                'last_login_at' => now(),
                'email_verified_at' => now(),
            ]);

            return [
                'access_token' => $token,
                'type_token' => 'Bearer',
            ];
        }

        throw new InputException(__('response.not_found'));
    }
}
