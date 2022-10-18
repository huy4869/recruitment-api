<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stores';

    protected $fillable = [
        'specialize_id',
        'manager_name',
        'recruiter_name',
        'postal_code',
        'province_id',
        'city',
        'address',
        'name',
        'website',
        'tel',
        'employee_quantity',
        'founded_year',
        'business_segment',
        'store_features',
        'created_by',
    ];

    /**
     * @return BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(MProvince::class, 'province_id');
    }

    /**
     * @return HasMany
     */
    public function jobs()
    {
        return $this->hasMany(JobPosting::class, 'store_id', 'id');
    }
}
