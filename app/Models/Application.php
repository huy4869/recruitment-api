<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\Application as ScopesApplication;

class Application extends Model
{
    use HasFactory, SoftDeletes, ScopesApplication;

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
        'interview_approach_id',
        'date',
        'note',
        'hours',
        'update_times',
        'checked_at',
        'owner_memo',
        'meet_link',
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
     * @return mixed
     */
    public function jobPostingAcceptTrashed()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * @return mixed
     */
    public function storeAcceptTrashed()
    {
        return $this->belongsTo(Store::class, 'store_id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function interviews()
    {
        return $this->belongsTo(MInterviewStatus::class, 'interview_status_id');
    }

    public function applicationUser()
    {
        return $this->hasOne(ApplicationUser::class);
    }

    public function applicationUserTrash()
    {
        return $this->hasOne(ApplicationUser::class)->withTrashed();
    }

    public function applicationUserWorkHistories()
    {
        return $this->hasMany(ApplicationUserWorkHistory::class);
    }

    public function applicationUserLearningHistories()
    {
        return $this->hasMany(ApplicationUserLearningHistory::class);
    }

    public function applicationUserLicensesQualifications()
    {
        return $this->hasMany(ApplicationUserLicensesQualification::class);
    }

    /**
     * @return BelongsTo
     */
    public function interviewApproach()
    {
        return $this->belongsTo(MInterviewApproach::class, 'interview_approach_id');
    }
}
