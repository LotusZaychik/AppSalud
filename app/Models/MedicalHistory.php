<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalHistory extends Model
{
    protected $table = 'medical_history';

    protected $fillable = [
        'user_id',
        'event_type',
        'title',
        'description',
        'event_date',
        'metadata'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'metadata' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
