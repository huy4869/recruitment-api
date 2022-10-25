<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MProvinceCity extends Model
{
    use HasFactory;

    protected $table = 'm_provinces_cities';

    protected $fillable = [
        'province_id',
        'name',
    ];

    /**
     * @return BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(MProvince::class, 'province_id');
    }
}
