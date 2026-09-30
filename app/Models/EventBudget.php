<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'category_id',
        'description',
        'estimated_amount',
        'actual_amount',
        'notes',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventExpenseCategory::class, 'category_id');
    }
}
