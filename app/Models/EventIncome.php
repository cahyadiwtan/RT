<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventIncome extends Model
{
    use HasFactory;

    protected $table = 'event_income';

    protected $fillable = [
        'event_id',
        'income_type',
        'description',
        'amount',
        'income_date',
        'source',
        'reference_number',
        'notes',
        'recorded_by',
        'status',
        'linked_payment_id',
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
