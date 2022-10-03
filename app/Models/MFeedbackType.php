<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MFeedbackType extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_feedback_types';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
