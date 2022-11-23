<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MInterviewStatus extends Model
{
    use HasFactory;

    public const STATUS_APPLYING = 1;
    public const STATUS_WAITING_INTERVIEW = 2;
    public const STATUS_WAITING_RESULT = 3;
    public const STATUS_ACCEPTED = 4;
    public const STATUS_REJECTED = 5;
    public const STATUS_CANCELED = 6;

    /**
     * @var string
     */
    protected $table = 'm_interviews_status';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
