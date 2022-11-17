<?php

namespace App\Models;

use App\Models\Scopes\User as ScopesUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, ScopesUser, SoftDeletes;

    protected $table = 'users';

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    public const ROLE_USER = 1;
    public const ROLE_RECRUITER = 2;
    public const ROLE_SUB_ADMIN = 3;
    public const ROLE_ADMIN = 4;

    // Gender
    public const GENDER_FEMALE = 1;
    // male
    public const GENDER_MALE = 2;
    // female
    public const GENDER_THIRD = 3;
    // different

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'store_id',
        'company_name',
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
        'province_city_id',
        'address',
        'building',
        'favorite_skill',
        'experience_knowledge',
        'self_pr',
        'home_page_recruiter',
        'motivation',
        'recent_jobs',
        'employee_quantity',
        'manager_name',
        'founded_year',
        'capital_stock',
        'noteworthy',
        'last_login_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'recent_jobs' => 'array',
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

    protected $appends = ['full_name', 'full_name_furi'];

    public function getFullNameAttribute()
    {
        return $this->first_name . $this->last_name;
    }

    public function getFullNameFuriAttribute()
    {
        return $this->furi_first_name . $this->furi_last_name;
    }

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
    public function provinceCity()
    {
        return$this->belongsTo(MProvinceCity::class, 'province_city_id');
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
        return $this->hasMany(UserWorkHistory::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return HasMany
     */
    public function favoriteJobs()
    {
        return $this->hasMany(FavoriteJob::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'favorite_jobs');
    }

    /**
     * @return MorphMany
     */
    public function avatarDetails()
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', Image::AVATAR_DETAIL);
    }

    /**
     * @return MorphOne
     */
    public function avatarBanner()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', Image::AVATAR_BANNER);
    }

    /**
     * @return MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * @return HasMany
     */
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * @return HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return HasOne
     */
    public function desiredConditionUser()
    {
        return $this->hasOne(DesiredConditionUser::class);
    }

    /**
     * @return BelongsTo
     */
    public function favoriteUser()
    {
        return $this->belongsTo(FavoriteUser::class, 'id', 'user_id');
    }

    /**
     * @return HasMany
     */
    public function stores()
    {
        return $this->hasMany(Store::class, 'user_id', 'id');
    }

    /**
     * @return HasManyThrough
     */
    public function jobsOwned()
    {
        return $this->hasManyThrough(JobPosting::class, Store::class);
    }

    public function getFullNameAddressAttribute()
    {
        $provinceName = $this->province->name ?? '';

        return sprintf('〒 %s %s%s%s', $this->postal_code, $provinceName, $this->address, $this->building);
    }

    public function recruiterOffTimes()
    {
        return $this->HasOne(RecruiterOffTime::class, 'user_id', 'id');
    }

    public function applicationUser()
    {
        return $this->hasMany(ApplicationUser::class);
    }

    /**
     * @return mixed|void
     */
    public function getAllOwnStoreNames()
    {
        $storeNames = $this->stores()->pluck('name')->toArray();

        return $storeNames ? implode('、', $storeNames) : null;
    }

    public function searchJobs()
    {
        return $this->hasMany(SearchJob::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(FeedbackJob::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function userJobDesiredMatches()
    {
        return $this->hasMany(UserJobDesiredMatch::class);
    }

    public function applicationUserLearningHistories()
    {
        return $this->hasMany(ApplicationUserLearningHistory::class);
    }

    public function applicationUserLicensesQualifications()
    {
        return $this->hasMany(ApplicationUserLicensesQualification::class);
    }

    public function applicationUserWorkHistories()
    {
        return $this->hasMany(ApplicationUserWorkHistory::class);
    }
}
