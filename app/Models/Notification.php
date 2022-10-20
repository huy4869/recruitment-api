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

    public const TYPE_INTERVIEW_COMING = 1;
    public const TYPE_INTERVIEW_SCHEDULE = 2;
    public const TYPE_NEW_MESSAGE = 3;
    public const TYPE_INTERVIEW_CHANGED = 4;
    public const TYPE_INTERVIEW_PENDING = 5;

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
}