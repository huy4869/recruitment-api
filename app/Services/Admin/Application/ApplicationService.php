<?php

namespace App\Services\Admin\Application;

use App\Exceptions\InputException;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApplicationService extends Service
{
    /**
     * @param $id
     * @return Builder|Model|object
     * @throws InputException
     */
    public function getDetail($id)
    {
        $application = Application::query()
            ->where('id', $id)
            ->with([
                'store',
                'applicationUser',
                'applicationUser.avatarDetails',
                'applicationUser.avatarBanner',
                'applicationUser.gender',
                'applicationUser.province',
                'applicationUser.provinceCity',
                'applicationUser.province.provinceDistrict',
                'jobPosting',
                'interviews',
            ])
            ->first();

        if ($application) {
            $application->update([
                'checked_at' => now()
            ]);

            return $application;
        }

        throw new InputException(trans('response.not_found'));
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * @throws InputException
     * @throws Exception
     */
    public function update($id, $data)
    {
        $application = Application::query()
            ->where('id', $id)
            ->with([
                'store',
                'interviews'
            ])
            ->first();

        if (!$application) {
            throw new InputException(trans('response.not_found'));
        }

        try {
            DB::beginTransaction();

            $application->update($data);

            $userNotifyData = [
                'user_id' => $application->user_id,
                'notice_type_id' => Notification::TYPE_INTERVIEW_CHANGED,
                'noti_object_ids' => [
                    'store_id' => $application->store_id,
                    'application_id' => $application->id,
                    'user_id' => $this->user->id
                ],
                'title' => trans('notification.N006.title', [
                    'store_name' => $application->store->name,
                ]),
                'content' => trans('notification.N006.content', [
                    'store_name' => $application->store->name,
                    'interview_status' => $application->interviews->name,
                ]),
            ];

            Notification::create($userNotifyData);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }//end try
    }
}
