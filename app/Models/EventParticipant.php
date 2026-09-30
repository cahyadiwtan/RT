<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'resident_id',
        'participation_status',
        'payment_required',
        'payment_amount',
        'payment_status',
        'registered_at',
        'notes',
    ];

    protected $casts = [
        'payment_required' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'registered_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function eventPayments(): HasMany
    {
        return $this->hasMany(EventPayment::class, 'event_participant_id');
    }
}
