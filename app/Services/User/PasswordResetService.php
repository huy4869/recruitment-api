<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\UrlHelper;
use App\Jobs\User\JobPasswordReset;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PasswordResetService extends Service
{
    /**
     * Send Mail Forgot Password
     *
     * @param $email
     * @return bool
     * @throws InputException
     */
    public function sendMail($email)
    {
        $user = User::query()->where('email', $email)->roleUser()->first();

        if (!$user) {
            throw new InputException(trans('validation.exists', [
                'attribute' => trans('validation.attributes.email')
            ]));
        }

        $token = Str::random(config('password_reset.token.length'));
        $url = UrlHelper::resetPasswordLink($token);

        $infoSendMail = [
            'email' => $user->email,
            'first_name' => $user->first_name,
            'subject' => trans('mail.forgot_password'),
            'url' => $url,
        ];

        dispatch(new JobPasswordReset($infoSendMail))->onQueue(config('queue.email_queue'));

        PasswordReset::updateOrCreate([
            'email' => $user->email,
        ], [
            'email' => $user->email,
            'token' => $token,
        ]);

        return true;
    }

    /**
     * Check token
     *
     * @param $token
     * @return bool
     */
    public function checkToken($token): bool
    {
        $timeCheck = config('password_reset.time_reset_pass');
        $date = date('Y-m-d H:i:s', strtotime('-' . $timeCheck .' minutes', time()));
        $passwordReset = PasswordReset::query()->where('token', $token)->where('created_at', '>=', $date)->first();

        return !!$passwordReset;
    }

    /**
     * reset password
     *
     * @param $data
     * @return bool
     * @throws InputException
     */
    public function resetPassword($data)
    {
        $timeCheck = config('password_reset.time_reset_pass');
        $date = date('Y-m-d H:i:s', strtotime('-' . $timeCheck .' minutes', time()));
        $passwordReset = PasswordReset::query()->where('token', $data['token'])->where('created_at', '>=', $date)->first();
        if (!$passwordReset) {
            throw new InputException(trans('response.invalid_token'));
        }

        $user = User::query()->where('email', $passwordReset['email'])->roleUser()->first();
        if (!$user) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();
            $user->update([
                'password' => Hash::make($data['password'])
            ]);
            PasswordReset::query()->where('token', $data['token'])->delete();
            $user->tokens()->delete();

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            throw new InputException($exception->getMessage());
        }
    }
}