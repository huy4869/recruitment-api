<?php

namespace App\Models;

use App\Models\Scopes\JobPosting as ScopesJobPosting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes, ScopesJobPosting;

    public const STATUS_DRAFT = 1;
    public const STATUS_RELEASE = 2;
    public const STATUS_END = 3;

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
        'province_id',
        'city',
        'address',
        'stations',
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
        'released_at'
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
        'stations' => 'array',
        'feature_ids' => 'array',
        'experience_ids' => 'array',
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
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
}
