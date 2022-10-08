<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPositionOffice extends Model
{
    use HasFactory;

    protected $table = 'm_position_offices';

    protected $fillable = ['name'];
}
