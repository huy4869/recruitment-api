<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notifications';

    public const STATUS_UNREAD = 0;
    public const STATUS_READ = 1;

    public const STATUS_NOT_ANNOUNCE = 0;
    public const STATUS_ANNOUNCE = 1;

    public const TYPE_INTERVIEW_COMING = 1;
    public const TYPE_CANCEL_APPLY = 2;
    public const TYPE_NEW_MESSAGE = 3;
    public const TYPE_INTERVIEW_CHANGED = 4;
    public const TYPE_DELETE_JOB = 5;
    public const TYPE_DELETE_USER = 6;
    public const TYPE_DELETE_RECRUITER = 7;
    public const TYPE_DELETE_STORE = 8;
    public const TYPE_MATCHING_FAVORITE = 9;
    public const TYPE_INTERVIEW_APPLY= 10;
    public const TYPE_UPDATE_INTERVIEW_APPLY= 11;
    public const TYPE_ADMIN_CHANGE_INTERVIEW_STATUS = 12;
    public const TYPE_WAIT_INTERVIEW_LIMIT_DATE = 13;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'notice_type_id',
        'noti_object_ids',
        'title',
        'content',
        'be_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'noti_object_ids' => 'array',
    ];

    public function noticeType()
    {
        return $this->belongsTo(MNoticeType::class, 'notice_type_id');
    }
}
