<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Helpers\UrlHelper;
use App\Jobs\User\JobPasswordReset;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\Service;
use Illuminate\Http\JsonResponse;
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
            return false;
        }

        if (is_null($user->email_verified_at)) {
            throw new InputException(__('validation.ERR.051'));
        }

        $token = Str::random(config('password_reset.token.length'));
        $url = UrlHelper::resetPasswordLink($token, $user);

        $infoSendMail = [
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'subject' => trans('mail.subject.forgot_password'),
            'url' => $url,
        ];

        dispatch(new JobPasswordReset($infoSendMail))->onQueue(config('queue.email_queue'));

        PasswordReset::updateOrCreate([
            'email' => $user->email,
            'role_id' => $user->role_id,
        ], [
            'email' => $user->email,
            'role_id' => $user->role_id,
            'token' => $token,
        ]);

        return true;
    }

    /**
     * Check token
     *
     * @param $token
     * @return bool
     * @throws InputException
     */
    public function checkToken($token): bool
    {
        $timeCheck = config('password_reset.time_reset_pass');
        $date = date('Y-m-d H:i:s', strtotime('-' . $timeCheck .' minutes', time()));
        $passwordReset = PasswordReset::query()->where('token', $token)->first();

        if (!$passwordReset) {
            throw new InputException(trans('validation.ERR.048'));
        }

        if ($passwordReset->updated_at < $date) {
            throw new InputException(trans('validation.ERR.047'));
        }

        return true;
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
        $passwordReset = PasswordReset::query()->where('token', $data['token'])->first();

        if (!$passwordReset) {
            throw new InputException(trans('validation.ERR.048'));
        }

        if ($passwordReset->updated_at < $date) {
            throw new InputException(trans('validation.ERR.047'));
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
