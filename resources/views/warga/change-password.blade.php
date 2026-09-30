@extends('layouts.app')

@section('title', 'Ubah Password — Sistem RT')

@section('content')
<div class="max-w-md mx-auto my-6">
    <div class="bg-surface border border-amber/10 rounded-2xl p-6 shadow-xl">
        <h1 class="text-xl font-bold text-warm mb-2">Ubah Password Akun</h1>
        <p class="text-xs text-warm/50 mb-6">Perbarui password akun warga Anda untuk menjaga keamanan</p>

        <form action="{{ route('warga.update-password') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Password Saat Ini</label>
                <input type="password" name="current_password" required placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Password Baru</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            </div>

            <div>
                <label class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required placeholder="Ketik ulang password baru"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink border border-amber/10 text-warm text-sm focus:ring-2 focus:ring-jade/50">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-amber/10">
                <button type="submit" class="w-full py-3 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition shadow-md">
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
