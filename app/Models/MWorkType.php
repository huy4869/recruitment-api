<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MWorkType extends Model
{
    use HasFactory;

    public const OTHER = 5;

    public const NO_DEFAULT = 0;
    public const IS_DEFAULT = 1;

    /**
     * @var string
     */
    protected $table = 'm_work_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'is_default'];
}
