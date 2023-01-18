<?php

namespace App\Services\User;

use App\Jobs\User\MailContactJob;
use App\Models\Contact;
use App\Models\User;
use App\Services\Service;

class ContactService extends Service
{
    /**
     * Create contact
     *
     * @param $data
     * @return mixed
     */
    public function store($data)
    {
        $data = array_merge($data, [
            'user_id' => $this->user->id ?? null,
        ]);
        $user = $this->user;
        dispatch(new MailContactJob($data, $user))->onQueue(config('queue.email_queue'));

        return Contact::create($data);
    }

    public function getAdminPhone()
    {
        $admin = User::query()->where('role_id', User::ROLE_ADMIN)->first();

        return $admin->tel ?? null;
    }
}
