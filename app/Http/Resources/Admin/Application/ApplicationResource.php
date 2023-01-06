<?php

namespace App\Http\Resources\Admin\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'interview' => [
                'id' => $this->interviews->id,
                'name' => $this->interviews->name,
            ],
            'job' => [
                'id' => $this->job_id,
                'name' => $this->job_name,
            ],
            'user' => [
                'id' => $this->user_id,
                'avatar_banner' => $this->is_public_avatar == User::STATUS_PUBLIC_AVATAR
                    ? FileHelper::getFullUrl(@$this->applicationUser->avatarBanner->url)
                    : null,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'furi_first_name' => $this->furi_first_name,
                'furi_last_name' => $this->furi_last_name,
                'age' => DateTimeHelper::birthDayByAge($this->birthday, $this->created_at),
            ],
            'be_read' => in_array($this->id, Auth::user()->be_read_applications ?? []),
            'created_at' => DateTimeHelper::formatDateDayOfWeekJa($this->created_at),
            'owner' => [
                'first_name' => $this->owner_first_name,
                'last_name' => $this->owner_last_name,
                'store_name' => $this->store_name,
            ],
            'is_deleted' => !is_null($this->deleted_at),
        ];
    }
}
