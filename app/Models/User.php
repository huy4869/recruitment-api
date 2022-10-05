<?php

namespace App\Models;

use App\Models\Scopes\User as ScopesUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'first_name',
        'last_name',
        'furi_first_name',
        'furi_last_name',
        'alias_name',
        'birthday',
        'age',
        'gender_id',
        'tel',
        'email_verified_at',
        'line',
        'facebook',
        'instagram',
        'twitter',
        'postal_code',
        'province_id',
        'city',
        'address',
        'favorite',
        'skill',
        'experience',
        'knowledge',
        'selft_pr',
        'desire_job_type_ids',
        'desire_from_working',
        'desire_to_working',
        'desire_job_work_id',
        'desire_from_salary',
        'desire_to_salary',
        'experience_id',
        'home_page_rescuiter',
        'motivation',
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

    /**
     * @return BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(MRole::class, 'role_id');
    }

    /**
     * @return BelongsTo
     */
    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    /**
     * @return BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(MProvince::class, 'province_id');
    }

    /**
     * @return BelongsTo
     */
    public function experience()
    {
        return $this->belongsTo(MJobExperience::class, 'experience_id');
    }

    /**
     * @return HasMany
     */
    public function userLearningHistories()
    {
        return $this->hasMany(UserLearningHistory::class);
    }

    /**
     * @return HasMany
     */
    public function userLicensesQualifications()
    {
        return $this->hasMany(UserLicensesQualification::class);
    }

    /**
     * @return HasMany
     */
    public function userWordHistories()
    {
        return $this->hasMany(UserWorkHistory::class);
    }

    /**
     * @return HasMany
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
