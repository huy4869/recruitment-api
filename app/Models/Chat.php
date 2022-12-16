<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    public const FROM_USER = [
        'TRUE' => 1,
        'FALSE' => 0,
    ];
    public const APPLY_MESSAGE = [
        'FROM_USER' => 0,
        'FROM_REC' => 1,
    ];
    public const BE_READED = 1;
    public const UNREAD = 0;

    /**
     * @var string
     */
    protected $table = 'chats';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'store_id',
        'is_from_user',
        'be_readed',
        'content',
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

    /**
     * @return BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function storeTrashed()
    {
        return $this->belongsTo(Store::class, 'store_id')->withTrashed();
    }
}
