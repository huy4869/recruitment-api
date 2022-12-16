<?php

namespace App\Models;

use App\Models\Scopes\JobPosting as ScopesJobPosting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes, ScopesJobPosting;

    public const STATUS_DRAFT = 1;
    public const STATUS_RELEASE = 2;
    public const STATUS_HIDE = 3;
    public const STATUS_END = 4;

    public const FULL_DAY = 1;
    public const HALF_DAY = 2;

    public const TYPE_MORNING = 1;
    public const TYPE_AFTERNOON = 2;

    /**
     * @var string
     */
    protected $table = 'job_postings';

    /**
     * @var string[]
     */
    protected $fillable = [
        'store_id',
        'job_type_ids',
        'work_type_ids',
        'job_status_id',
        'postal_code',
        'province_city_id',
        'province_id',
        'address',
        'building',
        'station_ids',
        'name',
        'pick_up_point',
        'description',
        'welfare_treatment_description',
        'salary_min',
        'salary_max',
        'salary_type_id',
        'salary_description',
        'start_work_time',
        'end_work_time',
        'shifts',
        'gender_ids',
        'holiday_description',
        'feature_ids',
        'experience_ids',
        'age_min',
        'age_max',
        'views',
        'created_by',
        'released_at',
        'working_days',
        'range_hours_type',
        'start_work_time_type',
        'end_work_time_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'job_type_ids' => 'array',
        'work_type_ids' => 'array',
        'gender_ids' => 'array',
        'station_ids' => 'array',
        'feature_ids' => 'array',
        'experience_ids' => 'array',
        'working_days' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * @return BelongsTo
     */
    public function storeTrashed()
    {
        return $this->belongsTo(Store::class, 'store_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MJobStatus::class, 'job_status_id');
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
    public function salaryType()
    {
        return $this->belongsTo(MSalaryType::class, 'salary_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return MorphOne
     */
    public function bannerImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', 'job_banner');
    }

    /**
     * @return MorphOne
     */
    public function bannerImageAcceptTrashed()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', 'job_banner')->withTrashed();
    }

    /**
     * @return MorphMany
     */
    public function detailImages()
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', 'job_detail');
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
     * @return HasMany
     */
    public function userJobDesiredMatch()
    {
        return $this->hasMany(UserJobDesiredMatch::class, 'job_id', 'id');
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
    public function feedbacks()
    {
        return $this->hasMany(FeedbackJob::class, 'job_posting_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function applicationUserWorkHistory()
    {
        return $this->hasMany(ApplicationUserWorkHistory::class, 'job_posting_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function applicationUserLearningHistory()
    {
        return $this->hasMany(ApplicationUserLearningHistory::class, 'job_posting_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function provinceCity()
    {
        return $this->belongsTo(MProvinceCity::class, 'province_city_id');
    }
}
