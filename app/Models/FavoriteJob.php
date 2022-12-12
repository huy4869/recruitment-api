<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FavoriteJob extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'favorite_jobs';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'job_posting_id',
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
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id')->released();
    }

    /**
     * @return BelongsTo
     */
    public function jobPostingTrashed()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id')->withTrashed();
    }
}
