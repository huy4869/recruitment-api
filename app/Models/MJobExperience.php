<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobExperience extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_job_experiences';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * @return string
     */
    public static function getTableName()
    {
        return (new self)->getTable();
    }
}
