<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobStatus extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_job_statuses';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
