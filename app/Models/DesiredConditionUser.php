<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'province_id',
        'work_type_ids',
        'age',
        'salary_type_id',
        'salary_min',
        'salary_max',
        'job_type_ids',
        'job_experience_ids',
        'job_feature_ids',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
