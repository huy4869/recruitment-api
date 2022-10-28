<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationUserLearningHistory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'application_user_learning_histories';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'application_id',
        'learning_status_id',
        'school_name',
        'enrollment_period_start',
        'enrollment_period_end',
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
    public function learningStatus()
    {
        return $this->belongsTo(MLearningStatus::class, 'learning_status_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
