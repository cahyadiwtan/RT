<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'category_id',
        'name',
        'description',
        'quantity',
        'unit',
        'condition',
        'status',
        'location',
        'acquisition_date',
        'acquisition_source',
        'acquisition_price',
        'current_value',
        'brand',
        'model',
        'serial_number',
        'photo',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'acquisition_date' => 'date',
        'acquisition_price' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class);
    }
}
