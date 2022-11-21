<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FavoriteUser extends Model
{
    use HasFactory, SoftDeletes;

    const UNFAVORITE_USER = 0;
    const FAVORITE_USER = 1;

    /**
     * @var string
     */
    protected $table = 'favorite_users';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'favorite_user_id',
    ];
}
