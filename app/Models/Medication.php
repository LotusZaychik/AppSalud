<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medication extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'dosage',
        'reminder_times',
        'frequency',
        'start_date',
        'end_date',
        'notes',
        'is_active',
        'type',
        'category',
        'duration_days'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'reminder_times' => 'array',
        'duration_days' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
