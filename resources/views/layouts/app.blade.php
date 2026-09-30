<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem RT Monolith')</title>
    @stack('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: 'var(--color-ink)',
                        surface: 'var(--color-surface)',
                        warm: 'var(--color-warm)',
                        amber: 'var(--color-amber)',
                        jade: 'var(--color-jade)',
                        clay: 'var(--color-clay)',
                    },
                    fontFamily: {
                        display: ['"DM Serif Display"', 'serif'],
                        sans: ['"Inter"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --color-ink: #f8f6f3;
            --color-surface: #ffffff;
            --color-warm: #2d2a3e;
            --color-amber: #b8892d;
            --color-jade: #3a7a5a;
            --color-clay: #a85f3f;
            --gelar-gradient: linear-gradient(to right, rgb(184, 137, 45), transparent);
            --card-glow-hover: 0 0 24px rgba(184, 137, 45, 0.15);
            --table-head-bg: linear-gradient(to right, rgba(255, 255, 255, 0.95), rgba(248, 246, 243, 0.8));
            --body-bg: #f8f6f3;
            --body-text: #2d2a3e;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--body-bg); color: var(--body-text); }
        .gelar { position: relative; padding-top: 0.5rem; }
        .gelar::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--gelar-gradient); }
        .gelar-label { font-size: 0.625rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-amber); }
        .card-amber { transition: box-shadow 0.3s ease, border-color 0.3s ease; }
        .card-amber:hover { border-color: rgba(184, 137, 45, 0.3); box-shadow: var(--card-glow-hover); }
        .table-head { background: var(--table-head-bg); }
        .arrear-row { background: rgba(45, 42, 62, 0.05); border: 1px solid rgba(45, 42, 62, 0.10); border-radius: 0.75rem; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; }
        .arrear-badge { font-size: 10px; padding: 2px 8px; border-radius: 9999px; font-weight: 600; }
        .arrear-badge--unpaid { background: rgba(168, 95, 63, 0.10); border: 1px solid rgba(168, 95, 63, 0.30); color: var(--color-clay); }
        .arrear-badge--partial { background: rgba(184, 137, 45, 0.10); border: 1px solid rgba(184, 137, 45, 0.30); color: var(--color-amber); }
    </style>
</head>
<body class="min-h-screen antialiased" x-data="{ sidebarOpen: false }">

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" x-cloak
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden">
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed top-0 left-0 z-50 h-full w-64 bg-surface border-r border-amber/10 flex flex-col transition-transform duration-300 ease-in-out lg:z-30">

        <!-- Brand -->
        <div class="p-5 border-b border-amber/10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber to-amber/70 flex items-center justify-center text-ink font-bold text-lg shadow-lg shadow-amber/20 font-display shrink-0">
                    RT
                </div>
                <div>
                    <span class="font-display text-lg text-warm tracking-tight block leading-none">Sistem RT</span>
                    <span class="text-[10px] text-amber font-semibold tracking-wider uppercase">Transparansi</span>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            @auth
                @php $active = fn($route) => request()->routeIs($route) ? 'bg-amber/10 text-amber font-semibold' : 'text-warm/60 hover:bg-warm/5 hover:text-warm'; @endphp

                <a href="{{ route('dashboard') }}" class="{{ $active('dashboard') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                @if(Auth::user()->isPengurus())
                    <div class="pt-3 pb-1 px-3">
                        <span class="text-[10px] font-semibold text-amber/60 uppercase tracking-widest">Data Master</span>
                    </div>
                    <a href="{{ route('pengurus.houses.index') }}" class="{{ $active('pengurus.houses.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Data Rumah
                    </a>
                    <a href="{{ route('pengurus.residents.index') }}" class="{{ $active('pengurus.residents.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Data Warga
                    </a>
                    <a href="{{ route('pengurus.users.index') }}" class="{{ $active('pengurus.users.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Pengguna Akun
                    </a>

                    <div class="pt-3 pb-1 px-3">
                        <span class="text-[10px] font-semibold text-amber/60 uppercase tracking-widest">Keuangan</span>
                    </div>
                    <a href="{{ route('pengurus.fee-settings.index') }}" class="{{ $active('pengurus.fee-settings.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tarif Iuran
                    </a>
                    <a href="{{ route('pengurus.bills.index') }}" class="{{ $active('pengurus.bills.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Tagihan
                    </a>
                    <a href="{{ route('pengurus.payments.index') }}" class="{{ $active('pengurus.payments.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Pembayaran
                    </a>
                    <a href="{{ route('pengurus.incomes.index') }}" class="{{ request()->routeIs('pengurus.incomes.*') ? 'bg-jade/10 text-jade font-semibold' : 'text-warm/60 hover:bg-warm/5 hover:text-warm' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Pemasukan
                    </a>
                    <a href="{{ route('pengurus.income-categories.index') }}" class="{{ request()->routeIs('pengurus.income-categories.*') ? 'bg-jade/10 text-jade font-semibold' : 'text-warm/50 hover:bg-warm/5 hover:text-warm' }} flex items-center gap-3 pl-11 pr-3 py-2 rounded-xl text-xs font-medium transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"/></svg>
                        Kategori Pemasukan
                    </a>
                    <a href="{{ route('pengurus.expenses.index') }}" class="{{ request()->routeIs('pengurus.expenses.*') ? 'bg-amber/10 text-amber font-semibold' : 'text-warm/60 hover:bg-warm/5 hover:text-warm' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h2l1.5-1.5a1 1 0 01.707-.293H14.5a1 1 0 01.707.293L16.5 6H19a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7z"/></svg>
                        Pengeluaran
                    </a>
                    <a href="{{ route('pengurus.expense-categories.index') }}" class="{{ request()->routeIs('pengurus.expense-categories.*') ? 'bg-amber/10 text-amber font-semibold' : 'text-warm/50 hover:bg-warm/5 hover:text-warm' }} flex items-center gap-3 pl-11 pr-3 py-2 rounded-xl text-xs font-medium transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"/></svg>
                        Kategori Pengeluaran
                    </a>

                    <div class="pt-3 pb-1 px-3">
                        <span class="text-[10px] font-semibold text-amber/60 uppercase tracking-widest">Operasional</span>
                    </div>
                    <a href="{{ route('pengurus.events.index') }}" class="{{ request()->routeIs('pengurus.events.*') ? 'bg-amber/10 text-amber font-semibold' : 'text-warm/60 hover:bg-warm/5 hover:text-warm' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Event
                    </a>
                    <a href="{{ route('pengurus.aspirations.index') }}" class="{{ $active('pengurus.aspirations.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Aspirasi
                    </a>
                    <a href="{{ route('pengurus.assets.index') }}" class="{{ $active('pengurus.assets.*') || request()->routeIs('pengurus.categories.*') ? 'bg-amber/10 text-amber font-semibold' : 'text-warm/60 hover:bg-warm/5 hover:text-warm' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Inventaris
                    </a>
                    <a href="{{ route('pengurus.reports.index') }}" class="{{ $active('pengurus.reports.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Laporan
                    </a>
                    <a href="{{ route('pengurus.rt-profile.index') }}" class="{{ $active('pengurus.rt-profile.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Identitas RT
                    </a>
                @endif

                @if(Auth::user()->isWarga())
                    <a href="{{ route('warga.iuran') }}" class="{{ $active('warga.iuran') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Status Iuran
                    </a>
                    <a href="{{ route('warga.riwayat-pembayaran') }}" class="{{ $active('warga.riwayat-pembayaran') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Riwayat Pembayaran
                    </a>
                    <a href="{{ route('warga.cashflow') }}" class="{{ $active('warga.cashflow') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v4a2 2 0 002 2h4M14 3h4a2 2 0 012 2v9M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2v-6m-7 1v6m-4-8v6m0-4h6"/></svg>
                        Arus Kas
                    </a>
                    <a href="{{ route('warga.aspirations.index') }}" class="{{ $active('warga.aspirations.*') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Aspirasi Saya
                    </a>
                    <a href="{{ route('warga.assets') }}" class="{{ $active('warga.assets') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Inventaris
                    </a>
                    <a href="{{ route('warga.events.index') }}" class="{{ request()->routeIs('warga.events.*') ? 'bg-amber/10 text-amber font-semibold' : 'text-warm/60 hover:bg-warm/5 hover:text-warm' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Event
                    </a>
                    <div class="pt-3 pb-1 px-3">
                        <span class="text-[10px] font-semibold text-amber/60 uppercase tracking-widest">Akun</span>
                    </div>
                    <a href="{{ route('warga.change-password') }}" class="{{ $active('warga.change-password') }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Ubah Password
                    </a>
                @endif
            @endauth
        </nav>

        <!-- User / Logout -->
        @auth
        <div class="p-4 border-t border-amber/10">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-amber/10 flex items-center justify-center text-amber font-bold text-sm shrink-0">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <span class="block text-sm font-semibold text-warm truncate">{{ Auth::user()->username }}</span>
                    <span class="block text-[11px] text-amber capitalize">{{ Auth::user()->role }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-3 py-2 rounded-xl bg-clay/10 text-clay hover:bg-clay/20 text-xs font-semibold border border-clay/30 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
        @endauth
    </aside>

    <!-- Main Content Area -->
    <div class="lg:ml-64 min-h-screen flex flex-col">

        <!-- Mobile Top Bar -->
        <header class="lg:hidden sticky top-0 z-30 bg-surface/90 backdrop-blur-md border-b border-amber/10 px-4 h-14 flex items-center justify-between">
            <button @click="sidebarOpen = true" class="p-2 rounded-lg text-warm/70 hover:bg-warm/5 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber to-amber/70 flex items-center justify-center text-ink font-bold text-sm font-display">RT</div>
                <span class="font-display text-sm text-warm">Sistem RT</span>
            </a>
        </header>

        <!-- Main Content -->
        <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-jade/10 border border-jade/30 text-jade text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-jade shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-clay/10 border border-clay/30 text-clay text-sm leading-relaxed">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-clay/10 border border-clay/30 text-clay text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-clay shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="border-t border-amber/10 bg-ink/60 py-6 text-center text-xs text-warm/40">
            &copy; {{ date('Y') }} Sistem Administrasi & Transparansi RT
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
