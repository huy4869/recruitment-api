<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MInterviewApproach extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_interview_approaches';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
