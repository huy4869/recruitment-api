<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DesiredConditionUser extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'desired_condition_users';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'province_ids',
        'work_type_ids',
        'age',
        'salary_type_id',
        'salary_min',
        'salary_max',
        'job_type_ids',
        'job_experience_ids',
        'job_feature_ids',
        'working_days',
        'start_working_time',
        'end_working_time',
    ];

    protected $casts = [
        'province_ids' => 'array',
        'work_type_ids' => 'array',
        'job_type_ids' => 'array',
        'job_experience_ids' => 'array',
        'job_feature_ids' => 'array',
        'working_days' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
}
