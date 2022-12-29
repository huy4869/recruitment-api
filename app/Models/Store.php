<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stores';

    protected $fillable = [
        'hex_color',
        'user_id',
        'specialize_ids',
        'manager_name',
        'recruiter_name',
        'postal_code',
        'province_id',
        'province_city_id',
        'address',
        'building',
        'name',
        'website',
        'tel',
        'application_tel',
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

    /**
     * @return HasManyThrough
     */
    public function applications()
    {
        return $this->HasManyThrough(Application::class, JobPosting::class);
    }

    /**
     * @return HasManyThrough
     */
    public function feedbacks()
    {
        return $this->HasManyThrough(FeedbackJob::class, JobPosting::class);
    }

    /**
     * @return HasManyThrough
     */
    public function jobImages()
    {
        return $this->HasManyThrough(
        Image::class,
        JobPosting::class,
        'store_id',
        'imageable_id',
        'id',
        'id'
    )->where('imageable_type', JobPosting::class);
    }

    public function getFullNameAddressAttribute()
    {
        $provinceName = $this->province->name ?? '';
        $formatPostalCode = sprintf('%s-%s', substr($this->postal_code, 0, 3), substr($this->postal_code, -4));

        return sprintf('〒%s %s%s%s', $formatPostalCode, $provinceName, $this->address, $this->building);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
