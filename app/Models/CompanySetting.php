<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'latitude',
        'longitude',
        'allowed_radius_meters',
        'work_start_time',
        'work_end_time',
    ];
}
