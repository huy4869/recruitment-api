<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationUser extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'application_users';

    /**
     * @var string[]
     */
    protected $fillable = [
        'application_id',
        'user_id',
        'role_id',
        'first_name',
        'last_name',
        'furi_first_name',
        'furi_last_name',
        'alias_name',
        'birthday',
        'age',
        'gender_id',
        'tel',
        'email',
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
        'motivation',
        'noteworthy',
        'skills',
        'is_public_avatar',
    ];

    protected $casts = [
        'skills' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    /**
     * @return BelongsTo
     */
    public function desireCity()
    {
        return $this->belongsTo(MProvince::class, 'desire_city_id');
    }

    /**
     * @return BelongsTo
     */
    public function desireJob()
    {
        return $this->belongsTo(MJobType::class, 'desire_job_id');
    }

    /**
     * @return BelongsTo
     */
    public function desireJobWork()
    {
        return $this->belongsTo(MWorkType::class, 'desire_job_work_id');
    }

    /**
     * @return BelongsTo
     */
    public function desireSalary()
    {
        return $this->belongsTo(MSalaryType::class, 'desire_salary');
    }

    /**
     * @return BelongsTo
     */
    public function experienceYear()
    {
        return $this->belongsTo(MJobExperience::class, 'experience_year');
    }

    public function getFullNameAttribute()
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }

    public function getFullNameFuriAttribute()
    {
        return sprintf('%s %s', $this->furi_first_name, $this->furi_last_name);
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
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
     * @return BelongsTo
     */
    public function provinceCity()
    {
        return $this->belongsTo(MProvinceCity::class, 'province_city_id', 'id');
    }

    /**
     * @return MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
