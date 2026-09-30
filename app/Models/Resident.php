<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'nik',
        'nomor_kk',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'tanggal_tinggal',
        'jenis_kelamin',
        'nomor_telepon',
        'email',
        'status_warga',
        'hubungan_dalam_keluarga',
        'is_verified',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_tinggal' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function aspirations(): HasMany
    {
        return $this->hasMany(Aspiration::class);
    }

    public function assetLoans(): HasMany
    {
        return $this->hasMany(AssetLoan::class);
    }

    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function eventPayments(): HasMany
    {
        return $this->hasMany(EventPayment::class);
    }
}
