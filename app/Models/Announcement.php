<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'priority',
        'employee_id',
        'date_gc',
        'date_ec',
    ];

    /**
     * ማስታወቂያው ለተወሰነ ሰራተኛ የተላከ ከሆነ ግንኙነቱን ያመጣል
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
