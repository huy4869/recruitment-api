<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MProvinceDistrict extends Model
{
    use HasFactory;

    CONST HOKKAIDO = 1;
    CONST TOHOKU = 2;
    CONST KANTO = 3;
    CONST CHUBU = 4;
    CONST KINKI = 5;
    CONST CHINA = 6;
    CONST SHIKOKU = 7;
    CONST KYUSHU_OKINAWA = 8;

    /**
     * @var string
     */
    protected $table = 'm_province_districts';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];

    public function provinces()
    {
        return $this->hasMany(MProvince::class, 'district_id', 'id');
    }
}
