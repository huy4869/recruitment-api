<?php

namespace App\Services\Recruiter;

use App\Models\User;
use App\Services\Service;

class ProfileService extends Service
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getInformation()
    {
        return User::with('stores', 'province')->where('id', $this->user->id)->get();
    }
}
