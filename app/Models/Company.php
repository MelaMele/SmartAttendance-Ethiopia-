<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'slug',
        'admin_phone',
        'admin_pin',
        'latitude',
        'longitude',
        'allowed_radius_meters',
        'work_start_time',
        'work_end_time',
        'status',
        'subscription_expires_at',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
