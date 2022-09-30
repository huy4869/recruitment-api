<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MInterviewStatus extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_interviews_status';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
