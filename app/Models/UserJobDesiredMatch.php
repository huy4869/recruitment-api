<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserJobDesiredMatch extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'user_job_desired_matches';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'job_id',
        'match_detail',
        'suitability_point',
        'created_at',
        'updated_at',
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
    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }
}
