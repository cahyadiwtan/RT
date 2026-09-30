<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'block',
        'house_number',
        'address',
        'status',
        'notes',
    ];

    public function residents(): HasMany
    {
        return $table = $this->hasMany(Resident::class);
    }

    public function monthlyBills(): HasMany
    {
        return $this->hasMany(MonthlyBill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getHouseCodeAttribute(): string
    {
        return strtoupper($this->block).sprintf('%02d', (int) $this->house_number);
    }

    public function getFullAddressAttribute(): string
    {
        return "Blok {$this->block} No. {$this->house_number}";
    }
}
