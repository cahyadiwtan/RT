<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtProfile extends Model
{
    protected $fillable = [
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kota',
        'provinsi',
        'ketua_rt',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Profil RT yang aktif (dipakai di kop laporan).
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->orderByDesc('id')->first()
            ?? static::orderByDesc('id')->first();
    }

    public function getLabelAttribute(): string
    {
        return "RT.{$this->rt} RW.{$this->rw}";
    }
}
