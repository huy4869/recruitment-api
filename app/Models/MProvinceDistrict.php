<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MProvinceDistrict extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_province_districts';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];
}
