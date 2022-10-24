<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_APPLYING = 1;
    public const STATUS_WAITING_INTERVIEW = 2;
    public const STATUS_WAITING_RESULT = 3;
    public const STATUS_REJECTED = 4;
    public const STATUS_ACCEPTED = 5;
    public const STATUS_CANCELED = 6;

    /**
     * @var string
     */
    protected $table = 'applications';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'job_posting_id',
        'store_id',
        'interview_status_id',
        'interview_approaches',
        'date',
        'note',
        'hours',
        'update_times',
    ];

    protected $casts = [
        'interview_approaches' => 'array',
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
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

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
    public function interviews()
    {
        return $this->belongsTo(MInterviewStatus::class, 'interview_status_id');
    }
}
