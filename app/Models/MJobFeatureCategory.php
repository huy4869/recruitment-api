<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MJobFeatureCategory extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_job_feature_categories';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
