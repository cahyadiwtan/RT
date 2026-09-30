@extends('layouts.app')

@section('title', 'Edit Pengguna — Pengurus RT')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-surface/80 border border-amber/10 rounded-2xl p-6 shadow-xl">
        <h1 class="text-xl font-bold text-warm mb-2">Edit Akun: {{ $user->username }}</h1>
        <p class="text-xs text-warm/50 mb-6">Update informasi akun. Kosongkan password jika tidak diubah.</p>

        @if($user->id === auth()->id())
            <div class="mb-6 p-4 rounded-xl bg-amber/10 border border-amber/30 text-amber text-xs leading-relaxed">
                Anda sedang mengedit akun sendiri. Role dan status akun tidak dapat diubah untuk mencegah kehilangan akses.
            </div>
        @endif

        <form action="{{ route('pengurus.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Username</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                <p class="text-[10px] text-warm/40 mt-1">Huruf, angka, titik, dan garis bawah saja.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Password Baru</label>
                    <input type="password" name="password" placeholder="Biarkan kosong jika tidak diubah"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" placeholder="Ulangi password baru"
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Role</label>
                    <select name="role" required {{ $user->id === auth()->id() ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50 {{ $user->id === auth()->id() ? 'opacity-50' : '' }}">
                        <option value="warga" {{ old('role', $user->role) === 'warga' ? 'selected' : '' }}>Warga</option>
                        <option value="pengurus" {{ old('role', $user->role) === 'pengurus' ? 'selected' : '' }}>Pengurus</option>
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role }}">
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Warga Terhubung</label>
                    <select name="resident_id" class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
                        <option value="">-- Tanpa Warga --</option>
                        @foreach($residents as $r)
                            <option value="{{ $r->id }}" {{ old('resident_id', $user->resident_id) == $r->id ? 'selected' : '' }}>
                                {{ $r->nama_lengkap }} ({{ $r->house ? $r->house->full_address : '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ $user->id === auth()->id() ? 'checked disabled' : (old('is_active', $user->is_active) ? 'checked' : '') }}
                    class="rounded bg-ink border-amber/10 text-jade {{ $user->id === auth()->id() ? 'opacity-50' : '' }}">
                <label for="is_active" class="text-xs text-warm/80 cursor-pointer">Akun aktif</label>
                @if($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-amber/10">
                <a href="{{ route('pengurus.users.index') }}" class="px-4 py-2 rounded-xl bg-warm/5 text-warm/80 text-sm font-semibold hover:bg-warm/10 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection