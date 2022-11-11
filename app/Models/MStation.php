<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MStation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'm_stations';

    /**
     * @var string[]
     */
    protected $fillable = [
        'province_name',
        'railway_name',
        'station_name',
    ];

    /**
     * @return string
     */
    public static function getTableName()
    {
        return (new self)->getTable();
    }
}
