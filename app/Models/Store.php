<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stores';

    protected $fillable = [
        'user_id',
        'specialize_ids',
        'manager_name',
        'recruiter_name',
        'postal_code',
        'province_id',
        'province_city_id',
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

    protected $casts = [
        'specialize_ids' => 'array',
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
    public function provinceCity()
    {
        return $this->belongsTo(MProvinceCity::class, 'province_city_id');
    }

    /**
     * @return BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(MProvince::class, 'province_id');
    }

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function storeBanner()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', Image::STORE_BANNER);
    }

    /**
     * @return HasMany
     */
    public function jobs()
    {
        return $this->hasMany(JobPosting::class, 'store_id', 'id');
    }

    public function getFullNameAddressAttribute()
    {
        $provinceName = $this->province->name ?? '';

        return sprintf('〒 %s %s%s%s', $this->postal_code, $provinceName, $this->city, $this->address);
    }
}
