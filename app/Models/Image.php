<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'images';

    public const AVATAR_BANNER = 'avatar_banner';
    public const AVATAR_DETAIL = 'avatar_detail';
    public const JOB_BANNER = 'job_banner';
    public const JOB_DETAIL = 'job_detail';
    public const STORE_BANNER = 'store_banner';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'url',
        'thumb',
        'type',
    ];

    /**
     * Get the parent imageable model (user or post).
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
