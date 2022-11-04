<?php

namespace App\Services\Admin\User;

use App\Exceptions\InputException;
use App\Jobs\Admin\User\JobStore;
use App\Jobs\Admin\User\JobUpdate;
use App\Models\MRole;
use App\Models\Store;
use App\Models\User;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService extends Service
{
    /**
     * Detail user
     *
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function detail($id)
    {
        $user = User::query()->where('id', $id)->with('stores')->first();

        if (!$user) {
            throw new InputException(trans('response.not_found'));
        }

        return $user;
    }

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function store($data)
    {
        if ($data['role_id'] != User::ROLE_RECRUITER && isset($data['store_ids'])) {
            unset($data['store_id']);
        }

        try {
            DB::beginTransaction();

            $newUser = User::create($data);

            if ($data['role_id'] == User::ROLE_RECRUITER && isset($data['store_ids'])) {
                Store::query()->whereIn('id', $data['store_ids'])
                ->update([
                    'user_id' => $newUser->id,
                ]);
            }

            dispatch(new JobStore($data))->onQueue(config('queue.email_queue'));

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function update($id, $data)
    {
        $admin = $this->user;
        $user = User::query()->where('id', $id)->with('role')->first();

        if (!$user || (
                $admin->role_id == User::ROLE_SUB_ADMIN
                && $user->role_id == User::ROLE_SUB_ADMIN
            )) {
            throw new InputException(trans('response.not_found'));
        }

        if ($user->role_id != User::ROLE_RECRUITER && isset($data['store_ids'])) {
            unset($data['store_id']);
        }

        try {
            DB::beginTransaction();

            $oldUserPassword = $user->password;
            $newUserPassword = $data['password'];
            $data['password'] = Hash::make($data['password']);
            $user->update($data);

            if ($user->role_id == User::ROLE_RECRUITER && isset($data['store_ids'])) {
                Store::query()->whereIn('id', $data['store_ids'])
                ->update([
                    'user_id' => $user->id,
                ]);
            }

            if (!Hash::check($newUserPassword, $oldUserPassword)) {
                dispatch(new JobUpdate([
                    'user' => $user,
                    'update_data' => $data
                ]))
                ->onQueue(config('queue.email_queue'));
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }

    /**
     * @return Builder[]
     */
    public static function getUserRoleIdCanModify($roleId)
    {
        $condition = [User::ROLE_ADMIN];

        if ($roleId == User::ROLE_SUB_ADMIN) {
            $condition[] = User::ROLE_SUB_ADMIN;
        }

        return MRole::query()->whereNot('id', $condition)
            ->pluck('id')
            ->toArray();
    }
}
