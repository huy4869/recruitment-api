<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedbackJob extends Model
{
    use HasFactory, SoftDeletes;

    public const FEEDBACK_TYPE_DESIRE_SALARY = 1;

    public const NOT_READ = 0;
    public const BE_READ = 1;

    /**
     * @var string
     */
    protected $table = 'feedback_jobs';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'feedback_type_ids',
        'job_posting_id',
        'type',
        'desired_salary',
        'content',
        'be_read'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'feedback_type_ids' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userTrashed()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}
