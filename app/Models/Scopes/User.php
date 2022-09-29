<?php

namespace App\Models\Scopes;

use App\Models\User as ModelUser;

trait User
{
    /**
     * Scope role user
     *
     * @param $query
     * @return mixed
     */
    protected function scopeRoleUser($query)
    {
        return $query->where('role_id', ModelUser::ROLE_USER);
    }
}
