<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MJobFeature extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'm_job_features';

    protected $fillable = [
        'name',
        'category'
    ];

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(MJobFeatureCategory::class, 'category_id');
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return (new self)->getTable();
    }
}
