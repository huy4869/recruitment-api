<?php

namespace App\Services\Recruiter;

use App\Jobs\Recruiter\JobContact;
use App\Models\Contact;
use App\Models\User;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Support\Facades\DB;

class ContactService extends Service
{
    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function store($data)
    {
        $recruiter = User::query()->where('id', $this->user->id)
            ->with([
                'stores' => function ($query) use ($data) {
                    return $query->where('id', $data['store_id']);
                }
            ])
            ->first();

        try {
            DB::beginTransaction();

            Contact::create($data);

            dispatch(new JobContact($data, $recruiter))->onQueue(config('queue.email_queue'));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @return HigherOrderBuilderProxy|mixed|null
     */
    public function getAdminPhone()
    {
        $admin = User::query()->where('role_id', User::ROLE_ADMIN)->first();

        return $admin->tel ?? null;
    }
}
