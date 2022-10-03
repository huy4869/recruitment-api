<?php

namespace App\Services\User;

use App\Exceptions\InputException;
use App\Helpers\UrlHelper;
use App\Jobs\User\JobPasswordReset;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\Service;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService extends Service
{
    /**
     * Send Mail Forgot Password
     *
     * @param $email
     * @return bool
     * @throws ValidationException
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
}
