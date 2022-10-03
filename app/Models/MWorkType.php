<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MWorkType extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_work_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
