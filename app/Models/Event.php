<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_code',
        'title',
        'slug',
        'description',
        'category',
        'location',
        'start_at',
        'end_at',
        'registration_deadline',
        'payment_deadline',
        'funding_type',
        'required_payment',
        'target_amount',
        'status',
        'visibility',
        'cover_image',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registration_deadline' => 'datetime',
        'payment_deadline' => 'datetime',
        'required_payment' => 'decimal:2',
        'target_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function eventPayments(): HasMany
    {
        return $this->hasMany(EventPayment::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(EventIncome::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(EventExpense::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(EventBudget::class);
    }

    public function getTotalIncomeAttribute(): float
    {
        $payments = (float) $this->eventPayments()->where('status', 'active')->sum('amount');
        $income = (float) $this->incomes()->where('status', 'active')->sum('amount');

        return $payments + $income;
    }

    public function getTotalExpenseAttribute(): float
    {
        return (float) $this->expenses()->where('status', 'active')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_income - $this->total_expense;
    }
}
