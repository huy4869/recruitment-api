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

    /**
     * Scope role recruiter
     *
     * @param $query
     * @return mixed
     */
    protected function scopeRoleRecruiter($query)
    {
        return $query->where('role_id', ModelUser::ROLE_RECRUITER);
    }

    /**
     * Scope role sub admin/admin
     *
     * @param $query
     * @return mixed
     */
    protected function scopeRoleAdmin($query)
    {
        return $query->whereIn('role_id', [ModelUser::ROLE_SUB_ADMIN, ModelUser::ROLE_ADMIN]);
    }
}
