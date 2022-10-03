<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MWorkPosition extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_work_positions';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
