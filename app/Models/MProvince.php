<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MProvince extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_learning_status';

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'district_id'];

    /**
     * @return BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(MProvinceDistrict::class, 'district_id');
    }
}
