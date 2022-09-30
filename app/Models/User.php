<?php

namespace App\Models;

use App\Models\Scopes\User as ScopesUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, ScopesUser;

    protected $table = 'users';

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    public const ROLE_USER = 1;
    public const ROLE_RECRUITER = 2;
    public const ROLE_SUB_ADMIN = 3;
    public const ROLE_ADMIN = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'alias_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
