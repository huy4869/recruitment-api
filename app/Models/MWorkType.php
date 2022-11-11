<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MWorkType extends Model
{
    use HasFactory;

    public const FULL_TIME_EMPLOYEE = 1;
    public const TEMPORARY_STAFF  = 2;
    public const CONTRACT_EMPLOYEE = 3;
    public const PART_TIME_EMPLOYEE = 4;
    public const OTHER = 5;

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
    protected $table = 'm_work_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'is_default'];
}
