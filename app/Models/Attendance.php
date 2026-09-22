<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date_gc',
        'date_ec',
        'check_in_at',
        'check_in_lat',
        'check_in_lng',
        'check_in_distance_meters',
        'check_out_at',
        'check_out_lat',
        'check_out_lng',
        'check_out_distance_meters',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'date_gc' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
