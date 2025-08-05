<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'doctor_name',
        'specialty',
        'appointment_date',
        'appointment_time',
        'location',
        'notes',
        'status',
        'reason'
    ];

    protected $casts = [
        'appointment_date' => 'date'
    ];

    /**
     * Relationship: Appointment belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now()->toDateString())
                    ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope: Filter completed appointments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
