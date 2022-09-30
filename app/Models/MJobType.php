<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobType extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_job_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
