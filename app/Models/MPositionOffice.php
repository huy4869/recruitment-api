<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPositionOffice extends Model
{
    use HasFactory;

    public const NO_DEFAULT = 0;
    public const IS_DEFAULT = 1;
    public const POSITION_MANAGER = 1;
    public const POSITION_STAFF = 2;
    public const POSITION_MANICURIST = 3;
    public const POSITION_HAIRSTYLE = 4;

    protected $table = 'm_position_offices';

    protected $fillable = ['name', 'is_default', 'created_by'];
}
