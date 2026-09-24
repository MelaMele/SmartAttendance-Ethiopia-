<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'title',
        'banner_image',
        'target_url',
        'placement',
        'is_active',
        'views_count',
        'clicks_count',
    ];
}
