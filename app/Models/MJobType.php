<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobType extends Model
{
    use HasFactory;

    CONST HAIR = 1;
    CONST NAIL = 2;
    CONST CHIRO_CAIRO_OXY_HOTBATH = 3;
    CONST FACIAL_BODY_REMOVAL = 4;
    CONST CLINIC = 5;
    CONST OTHER = 'other';

    CONST NO_DEFAULT = 0;
    CONST IS_DEFAULT = 1;

    /**
     * @var string
     */
    protected $table = 'm_job_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'is_default'];
}
