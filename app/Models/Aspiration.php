<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aspiration extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'title',
        'description',
        'category',
        'status',
        'attachment',
    ];

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(AspirationUpdate::class);
    }
}
