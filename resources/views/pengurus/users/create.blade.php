@extends('layouts.app')

@section('title', 'Tambah Pengguna — Pengurus RT')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-surface/80 border border-amber/10 rounded-2xl p-6 shadow-xl">
        <h1 class="text-xl font-bold text-warm mb-2">Tambah Akun Pengguna</h1>
        <p class="text-xs text-warm/50 mb-6">Akun digunakan untuk login ke sistem. Beri tahu warga/pengurus username & password-nya.</p>

        <form action="{{ route('pengurus.users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required placeholder="cth: A01, budi, bendahara01"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                <p class="text-[10px] text-warm/40 mt-1">Huruf, angka, titik, dan garis bawah saja.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Role</label>
                    <select name="role" required class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                        <option value="warga" {{ old('role', 'warga') === 'warga' ? 'selected' : '' }}>Warga</option>
                        <option value="pengurus" {{ old('role') === 'pengurus' ? 'selected' : '' }}>Pengurus</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Warga Terhubung (Opsional)</label>
                    <select name="resident_id" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                        <option value="">-- Tanpa Warga --</option>
                        @foreach($residents as $r)
                            <option value="{{ $r->id }}" {{ old('resident_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_lengkap }} ({{ $r->house ? $r->house->full_address : '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="rounded bg-ink border-amber/10 text-jade">
                <label for="is_active" class="text-xs text-warm/80 cursor-pointer">Aktifkan akun ini langsung</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-amber/10">
                <a href="{{ route('pengurus.users.index') }}" class="px-4 py-2 rounded-xl bg-warm/5 text-warm/80 text-sm font-semibold hover:bg-warm/10 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>
@endsection