<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'city',
        'address',
        'image_id',
        'achievement_imgs',
        'favorite',
        'skill',
        'experience',
        'knowledge',
        'self_pr',
        'desire_city_id',
        'desire_job_id',
        'desire_job_work_id',
        'desire_salary',
        'experience_year',
        'home_page_recruiter',
        'motivation',
        'noteworthy_things',
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
}
