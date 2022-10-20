<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FavoriteUser extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'favorite_users';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'favorite_ids',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'favorite_ids' => 'array',
    ];
}