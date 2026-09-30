@extends('layouts.app')

@section('title', 'Identitas RT - Pengurus RT')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-display text-warm tracking-tight">Identitas RT</h1>
        <p class="text-xs text-warm/50 mt-1">Dipakai di kop laporan rekapitulasi kependudukan</p>
    </div>

    <div class="bg-surface border border-amber/10 rounded-2xl p-5 sm:p-6 max-w-3xl">
        <form action="{{ route('pengurus.rt-profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider">RT</span>
                    <input type="text" name="rt" value="{{ old('rt', $profile?->rt) }}" required
                           class="mt-1 w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                    @error('rt') <span class="text-xs text-amber-400 mt-1 block">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider">RW</span>
                    <input type="text" name="rw" value="{{ old('rw', $profile?->rw) }}" required
                           class="mt-1 w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                    @error('rw') <span class="text-xs text-amber-400 mt-1 block">{{ $message }}</span> @enderror
                </label>
            </div>

            <label class="block">
                <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider">Kelurahan</span>
                <input type="text" name="kelurahan" value="{{ old('kelurahan', $profile?->kelurahan) }}" required
                       class="mt-1 w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                @error('kelurahan') <span class="text-xs text-amber-400 mt-1 block">{{ $message }}</span> @enderror
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="block">
                    <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider">Kecamatan</span>
                    <input type="text" name="kecamatan" value="{{ old('kecamatan', $profile?->kecamatan) }}" required
                           class="mt-1 w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                    @error('kecamatan') <span class="text-xs text-amber-400 mt-1 block">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider">Kota</span>
                    <input type="text" name="kota" value="{{ old('kota', $profile?->kota) }}" required
                           class="mt-1 w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                    @error('kota') <span class="text-xs text-amber-400 mt-1 block">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider">Provinsi</span>
                    <input type="text" name="provinsi" value="{{ old('provinsi', $profile?->provinsi) }}" required
                           class="mt-1 w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                    @error('provinsi') <span class="text-xs text-amber-400 mt-1 block">{{ $message }}</span> @enderror
                </label>
            </div>

            <label class="block">
                <span class="text-xs font-semibold text-warm/50 uppercase tracking-wider">Nama Ketua RT</span>
                <input type="text" name="ketua_rt" value="{{ old('ketua_rt', $profile?->ketua_rt) }}"
                       class="mt-1 w-full px-4 py-2 rounded-xl bg-ink border border-amber/10 text-warm text-sm">
                @error('ketua_rt') <span class="text-xs text-amber-400 mt-1 block">{{ $message }}</span> @enderror
            </label>

            <label class="flex items-center gap-2 text-sm text-warm/70">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $profile?->is_active ?? true) ? 'checked' : '' }}
                       class="rounded bg-ink border-amber/20 text-warm focus:ring-warm/30">
                Jadikan profil aktif
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-warm text-ink text-sm font-semibold transition">Simpan Identitas</button>
                <a href="{{ route('pengurus.reports.kependudukan') }}" class="px-4 py-2.5 rounded-xl text-warm/50 hover:text-warm text-sm">Lihat Laporan</a>
            </div>
        </form>
    </div>
</div>
@endsection
