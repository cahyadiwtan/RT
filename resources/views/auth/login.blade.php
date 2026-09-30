@extends('layouts.app')

@section('title', 'Login — Sistem RT')

@section('content')
<div class="max-w-md mx-auto my-12">
    <div class="bg-surface backdrop-blur-xl border border-amber/10 rounded-2xl p-8 shadow-2xl shadow-amber/20">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-display text-warm tracking-tight">Selamat Datang Kembali</h1>
            <p class="text-xs text-warm/50 mt-1.5">Masuk dengan Username Rumah / Pengurus Anda</p>
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

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="username" class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-2">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                    placeholder="Contoh: A01 atau admin"
                    class="w-full px-4 py-3 rounded-xl bg-ink/80 border border-amber/10 text-warm placeholder-warm/30 text-sm focus:outline-none focus:ring-2 focus:ring-amber/20 focus:border-amber transition">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-warm/80 uppercase tracking-wider mb-2">Password</label>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl bg-ink/80 border border-amber/10 text-warm placeholder-warm/30 text-sm focus:outline-none focus:ring-2 focus:ring-amber/20 focus:border-amber transition">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-warm/50 hover:text-warm/80">
                    <input type="checkbox" name="remember" class="rounded bg-ink border-amber/10 text-amber focus:ring-0">
                    Ingat Saya
                </label>
                <a href="{{ route('password.request') }}" class="text-jade hover:underline">Lupa Password?</a>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-jade to-jade/70 hover:from-jade/90 hover:to-jade/60 text-ink font-bold text-sm shadow-lg shadow-amber/20 transition transform active:scale-[0.98]">
                Masuk ke Aplikasi
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-amber/10 text-xs text-warm/50 leading-relaxed">
            <p class="font-semibold text-warm/80 mb-2">Petunjuk Login Manual / Demo:</p>
            <div class="grid grid-cols-2 gap-2 bg-ink/80 p-3 rounded-xl border border-amber/10">
                <div>
                    <span class="block text-jade font-bold">Akun Pengurus</span>
                    <span>User: <code class="text-warm/80">admin</code></span><br>
                    <span>Pass: <code class="text-warm/80">password</code></span>
                </div>
                <div>
                    <span class="block text-jade font-bold">Akun Warga</span>
                    <span>User: <code class="text-warm/80">A01</code></span><br>
                    <span>Pass: <code class="text-warm/80">password</code></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
