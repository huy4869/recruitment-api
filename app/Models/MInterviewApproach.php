<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MInterviewApproach extends Model
{
    use HasFactory;

    public const ONLINE_INTERVIEW = 1;
    public const IN_PERSON = 2;
    public const PHONE_INTERVIEW = 3;

    /**
     * @var string
     */
    protected $table = 'm_interview_approaches';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
