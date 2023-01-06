<?php

namespace App\Http\Resources\Recruiter\Application;

use App\Helpers\DateTimeHelper;
use App\Helpers\FileHelper;
use App\Models\User;
use App\Services\Recruiter\Application\ApplicationService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use function Illuminate\Events\queueable;

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
        $user = $this->applicationUser;

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
                'avatar_banner' => @$this->applicationUser->is_public_avatar == User::STATUS_PUBLIC_AVATAR ? FileHelper::getFullUrl(@$this->applicationUser->avatarBanner->url) : null,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'furi_first_name' => $user->furi_first_name,
                'furi_last_name' => $user->furi_last_name,
                'age' => DateTimeHelper::birthDayByAge($user->birthday, $this->created_at),
            ],
            'be_read' => in_array($this->id, Auth::user()->be_read_applications ?? []),
            'created_at' => DateTimeHelper::formatDateDayOfWeekJa($this->created_at),
            'is_delete' => !is_null($this->deleted_at),
        ];
    }
}
