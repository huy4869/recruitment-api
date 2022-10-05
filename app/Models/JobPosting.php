<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'job_postings';

    /**
     * @var string[]
     */
    protected $fillable = [
        'store_id',
        'job_type_id',
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
        'welfare_treatment_description',
        'feature_ids',
        'age_min',
        'age_max',
        'views',
        'created_by'
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
    public function jobType()
    {
        return $this->belongsTo(MJobType::class, 'job_type_id');
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
}
