<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MInterviewApproach extends Model
{
    use HasFactory;

    public const STATUS_INTERVIEW_ONLINE = 1;
    public const STATUS_INTERVIEW_DIRECT = 2;
    public const STATUS_INTERVIEW_PHONE = 3;

    /**
     * @var string
     */
    protected $table = 'm_interview_approaches';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
