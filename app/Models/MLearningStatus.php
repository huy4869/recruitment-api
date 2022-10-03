<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MLearningStatus extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_learning_status';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
