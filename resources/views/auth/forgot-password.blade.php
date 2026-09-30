@extends('layouts.app')

@section('title', 'Lupa Password — Sistem RT')

@section('content')
<div class="max-w-md mx-auto my-12">
    <div class="bg-surface backdrop-blur-xl border border-amber/10 rounded-2xl p-8 shadow-2xl shadow-amber/20">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-display text-warm tracking-tight">Reset Password Warga</h1>
            <p class="text-xs text-warm/50 mt-1.5">Verifikasi Username dan NIK 16 digit terdaftar Anda</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-clay/10 border border-clay/30 text-clay text-xs leading-relaxed">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Username Rumah</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required placeholder="Contoh: A01"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink/80 border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/20">
            </div>

            <div>
                <label for="nik" class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">NIK (16 Digit)</label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required maxlength="16" placeholder="Contoh: 3201010101010001"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink/80 border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/20">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Password Baru</label>
                <input type="password" id="password" name="password" required placeholder="Minimal 6 karakter"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink/80 border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/20">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ketik ulang password baru"
                    class="w-full px-4 py-2.5 rounded-xl bg-ink/80 border border-amber/10 text-warm text-sm focus:outline-none focus:ring-2 focus:ring-amber/20">
            </div>

            <button type="submit" class="w-full mt-2 py-3 px-4 rounded-xl bg-amber hover:bg-amber/90 text-ink font-bold text-sm transition">
                Reset Password Sekarang
            </button>
        </form>

        <div class="mt-6 text-center text-xs">
            <a href="{{ route('login') }}" class="text-warm/50 hover:text-warm/80">← Kembali ke Halaman Login</a>
        </div>
    </div>
</div>
@endsection
