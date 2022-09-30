<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MSalaryType extends Model
{
    use HasFactory;


    /**
     * @var string
     */
    protected $table = 'm_interviews';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'term',
        'currency'
    ];
}
