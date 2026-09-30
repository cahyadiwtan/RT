@extends('layouts.app')

@section('title', 'Tambah Warga — Pengurus RT')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-surface/80 border border-amber/10 rounded-2xl p-6 shadow-xl">
        <h1 class="text-xl font-bold text-warm mb-6">Tambah Data Warga Baru</h1>

        <form action="{{ route('pengurus.residents.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Rumah Domisili</label>
                <select name="house_id" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                    <option value="">-- Pilih Rumah --</option>
                    @foreach($houses as $h)
                        <option value="{{ $h->id }}" {{ old('house_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->full_address }} - Status: {{ $h->status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">NIK (16 Digit)</label>
                    <input type="text" name="nik" value="{{ old('nik') }}" required maxlength="16" placeholder="320101..."
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Nomor KK (16 Digit)</label>
                    <input type="text" name="nomor_kk" value="{{ old('nomor_kk') }}" maxlength="16" placeholder="320101..."
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Nama Sesuai KTP"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" placeholder="Jakarta"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Tanggal Tinggal</label>
                    <input type="date" name="tanggal_tinggal" value="{{ old('tanggal_tinggal') }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                    <p class="text-[10px] text-warm/40 mt-1">Tagihan iuran mulai bulan setelah tanggal ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Jenis Kelamin</label>
                    <select name="jenis_kelamin" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Nomor Telepon / WA</label>
                    <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}" placeholder="0812..."
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Email (Opsional)</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="warga@example.com"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Hubungan Dalam Keluarga</label>
                    <select name="hubungan_dalam_keluarga" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                        <option value="kepala_keluarga" {{ old('hubungan_dalam_keluarga') === 'kepala_keluarga' ? 'selected' : '' }}>Kepala Keluarga</option>
                        <option value="istri" {{ old('hubungan_dalam_keluarga') === 'istri' ? 'selected' : '' }}>Istri</option>
                        <option value="anak" {{ old('hubungan_dalam_keluarga') === 'anak' ? 'selected' : '' }}>Anak</option>
                        <option value="famili_lain" {{ old('hubungan_dalam_keluarga') === 'famili_lain' ? 'selected' : '' }}>Famili Lain</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Status Warga</label>
                    <select name="status_warga" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                        <option value="aktif" {{ old('status_warga') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="pindah" {{ old('status_warga') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                        <option value="meninggal" {{ old('status_warga') === 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                    </select>
                </div>
            </div>

            @include('pengurus.residents.partials.population-fields')

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_verified" name="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}
                    class="rounded bg-ink border-amber/10 text-jade">
                <label for="is_verified" class="text-xs text-warm/80 cursor-pointer">Langsung verifikasi NIK warga ini</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-amber/10">
                <a href="{{ route('pengurus.residents.index') }}" class="px-4 py-2 rounded-xl bg-warm/5 text-warm/80 text-sm font-semibold hover:bg-warm/10 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition">Simpan Warga</button>
            </div>
        </form>
    </div>
</div>
@endsection
