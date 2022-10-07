<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWorkHistory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'user_work_histories';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'job_type_id',
        'work_type_id',
        'store_name',
        'company_name',
        'period_start',
        'period_end',
        'position_offices',
        'business_content',
        'experience_accumulation',
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
    public function jobType()
    {
        return $this->belongsTo(MJobType::class, 'job_type_id');
    }

    /**
     * @return BelongsTo
     */
    public function workType()
    {
        return $this->belongsTo(MWorkType::class, 'work_type_id');
    }
}
