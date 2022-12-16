<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobType extends Model
{
    use HasFactory;

    public const HAIR = 1;
    public const NAIL = 2;
    public const CLINIC = 3;
    public const CHIRO_CAIRO_OXY_HOTBATH = 4;
    public const FACIAL_BODY_REMOVAL = 5;
    public const OTHER = 6;

    public const NO_DEFAULT = 0;
    public const IS_DEFAULT = 1;

    /**
     * @return string
     */
    public static function getTableName()
    {
        return (new self)->getTable();
    }

    /**
     * @var string
     */
    protected $table = 'm_job_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'is_default'];
}
