<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobType extends Model
{
    use HasFactory;

    public const HAIR = 1;
    public const NAIL = 2;
    public const CHIRO_CAIRO_OXY_HOTBATH = 3;
    public const FACIAL_BODY_REMOVAL = 4;
    public const CLINIC = 5;
    public const OTHER = 6;

    public const NO_DEFAULT = 0;
    public const IS_DEFAULT = 1;

    /**
     * @var string
     */
    protected $table = 'm_job_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'is_default'];
}
